<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Pembeli\CheckoutController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\WhatsappSession;
use App\Models\BotTemplate;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Cart;
use App\Models\UserAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CompanyProfile;
use App\Models\StoreSetting;
use App\Models\StoreHoliday;
use Carbon\Carbon;

class WhatsAppBotController extends Controller
{
    public function webhook(Request $request)
    {
        $sender = $request->input('sender');
        $message = trim((string) $request->input('message'));

        if (empty($sender) || $message === '') {
            return response()->json(['status' => 'ignored']);
        }

        // Normalisasi nomor WA ke format 628xxx
        $waNumber = preg_replace('/[^0-9]/', '', $sender);
        if (str_starts_with($waNumber, '0')) {
            $waNumber = '62' . substr($waNumber, 1);
        } elseif (!str_starts_with($waNumber, '62')) {
            $waNumber = '62' . $waNumber;
        }

        // Buat atau ambil user berdasarkan nomor WA
        $user = User::firstOrCreate(
            ['whatsapp_number' => $waNumber],
            [
                'name'     => 'Pelanggan ' . substr($waNumber, -4),
                'password' => Hash::make(Str::random(10)),
                'role'     => 'pelanggan'
            ]
        );

        // Gunakan setUser (stateless) alih-alih login() agar tidak butuh session store di route API
        Auth::setUser($user);

        // WAJIB sertakan last_interaction_at di array create karena kolom DB bersifat NOT NULL
        $session = WhatsappSession::firstOrCreate(
            ['whatsapp_number' => $waNumber],
            [
                'current_step'        => 'sapaan',
                'session_data'        => [],
                'last_interaction_at' => now(),
            ]
        );

        // Jika sesi sudah ada sebelumnya, tetap perbarui waktu interaksi terakhir
        if (!$session->wasRecentlyCreated) {
            $session->update(['last_interaction_at' => now()]);
        }

        $sessionData = is_array($session->session_data) ? $session->session_data : [];

        switch ($session->current_step) {
            case 'sapaan':
                if ($message == '1') {
                    $this->stepLihatKategori($waNumber, $session);
                } elseif ($message == '2') {
                    $this->stepLihatKeranjang($waNumber, $user, $session);
                } elseif ($message == '3') {
                    $this->stepRiwayatPesanan($waNumber, $user, $session, 'semua', 1);
                } elseif ($message == '4') {
                    $this->stepInstruksi($waNumber, $session);
                } else {
                    $this->stepSapaan($waNumber, $user, $session);
                }
                break;

            case 'instruksi':
                if ($message == '0') {
                    $this->stepSapaan($waNumber, $user, $session);
                } else {
                    $this->replyInvalid($waNumber);
                }
                break;

            case 'lihat_kategori':
                if ($message == '0') {
                    $this->stepSapaan($waNumber, $user, $session);
                } else {
                    $this->prosesPilihKategori($waNumber, $message, $session);
                }
                break;

            case 'lihat_varian':
                if ($message == '0') {
                    $this->stepLihatKategori($waNumber, $session);
                } else {
                    $this->prosesPilihVarian($waNumber, $message, $session);
                }
                break;

            case 'detail_varian':
                if ($message == '0') {
                    $productId = $sessionData['selected_product_id'] ?? null;
                    if ($productId) {
                        $this->stepLihatVarian($waNumber, $session, $productId);
                    } else {
                        $this->stepLihatKategori($waNumber, $session);
                    }
                } elseif ($message == '1') {
                    $this->stepInputQty($waNumber, $session);
                } else {
                    $this->replyInvalid($waNumber);
                }
                break;

            case 'input_qty':
                if ($message == '0') {
                    $this->stepLihatKategori($waNumber, $session);
                } elseif (is_numeric($message) && $message > 0) {
                    $this->prosesMasukKeranjang($waNumber, $user, (int) $message, $session);
                } else {
                    $reply = $this->getTemplate('input_qty_invalid')
                        ?? "Kak, tolong masukin angka jumlah yang valid ya (misal: 2). Atau ketik 0 buat batal.";
                    $this->sendMessage($waNumber, $reply);
                }
                break;

            case 'keranjang':
                if ($message == '0') {
                    $this->stepSapaan($waNumber, $user, $session);
                } elseif ($message == '1') {
                    $this->stepCheckoutNama($waNumber, $session);
                } elseif ($message == '2') {
                    $this->stepLihatKategori($waNumber, $session);
                } elseif ($message == '3') {
                    Cart::where('user_id', $user->id)->delete();
                    $reply = $this->getTemplate('keranjang_dikosongkan')
                        ?? "🗑️ Keranjang belanja kamu sudah dikosongkan ya Kak!";
                    $this->sendMessage($waNumber, $reply);
                    $this->stepSapaan($waNumber, $user, $session);
                } else {
                    $this->replyInvalid($waNumber);
                }
                break;

            case 'checkout_nama':
                if ($message == '0') {
                    $this->stepSapaan($waNumber, $user, $session);
                } else {
                    $sessionData['co_nama'] = $message;
                    $session->update(['session_data' => $sessionData]);
                    $this->stepCheckoutAlamat($waNumber, $user, $session);
                }
                break;

            case 'checkout_alamat':
                if ($message == '0') {
                    $this->stepSapaan($waNumber, $user, $session);
                } else {
                    $addresses = UserAddress::where('user_id', $user->id)->get();

                    if (is_numeric($message) && $message > 0 && $message <= $addresses->count()) {
                        $selectedAddr = $addresses[$message - 1];

                        if (!$selectedAddr->latitude || !$selectedAddr->longitude) {
                            $appUrl = rtrim(request()->getSchemeAndHttpHost(), '/');
                            $linkMaps = $appUrl . "/pin-lokasi/" . $selectedAddr->id . "?wa=" . $waNumber;

                            $reply = $this->getTemplate('alamat_belum_pin', ['link_maps' => $linkMaps])
                                ?? "Koordinat alamat ini belum diset Kak.\n\nSilakan klik link berikut untuk menentukan titik akurat di Maps:\n{$linkMaps}";
                            $this->sendMessage($waNumber, $reply);
                            return;
                        }

                        $sessionData['co_address_id'] = $selectedAddr->id;
                        $patokan = $selectedAddr->landmark ? " (Patokan: {$selectedAddr->landmark})" : "";
                        $sessionData['co_alamat'] = "{$selectedAddr->street_address}, {$selectedAddr->district}, {$selectedAddr->city}, {$selectedAddr->province}{$patokan}";
                        $sessionData['co_ro_id'] = $selectedAddr->rajaongkir_destination_id;
                        $sessionData['co_distance'] = $selectedAddr->distance_to_store;

                        $session->update(['session_data' => $sessionData]);
                        $this->stepCekOngkirDanJadwal($waNumber, $user, $session);
                    } else {
                        $this->prosesInputAlamatBaru($waNumber, $user, $message, $session);
                    }
                }
                break;

            case 'checkout_pilih_ekspedisi':
                if ($message == '0') {
                    $this->stepSapaan($waNumber, $user, $session);
                } else {
                    $rates = $sessionData['temp_rates'] ?? [];
                    $index = ((int) $message) - 1;
                    if (is_numeric($message) && isset($rates[$index])) {
                        $pilihan = $rates[$index];
                        $sessionData['co_is_internal'] = false;
                        $sessionData['co_shipping_cost'] = $pilihan['price'];
                        $sessionData['co_courier_name'] = $pilihan['company'];
                        $sessionData['co_shipping_service'] = "{$pilihan['type']} (Est: {$pilihan['duration']} Hari)";
                        $sessionData['co_tanggal'] = 'auto';
                        $sessionData['co_jam'] = '15:00:00';

                        unset($sessionData['temp_rates']);
                        $session->update(['session_data' => $sessionData]);
                        $this->stepCheckoutMetodeBayar($waNumber, $session);
                    } else {
                        $reply = $this->getTemplate('ekspedisi_invalid')
                            ?? "Pilihan kurir tidak valid Kak. Balas dengan nomor kurir yang ada di daftar, atau ketik 0 buat batal.";
                        $this->sendMessage($waNumber, $reply);
                    }
                }
                break;

            case 'checkout_tanggal':
                if ($message == '0') {
                    $this->stepSapaan($waNumber, $user, $session);
                } else {
                    $tanggalList = $sessionData['temp_tanggal_valid'] ?? [];
                    if (is_numeric($message) && !empty($tanggalList)) {
                        $index = ((int) $message) - 1;
                        if (isset($tanggalList[$index])) {
                            $sessionData['co_tanggal'] = $tanggalList[$index];
                            unset($sessionData['temp_tanggal_valid']);
                            $session->update(['session_data' => $sessionData]);
                            $this->stepCheckoutJam($waNumber, $session, true);
                            break;
                        }
                    }

                    if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $message)) {
                        $this->prosesValidasiTanggal($waNumber, $user, $message, $session);
                    } else {
                        $reply = $this->getTemplate('tanggal_format_salah')
                            ?? "Format atau pilihan tanggal belum sesuai Kak. Ketik dengan format *YYYY-MM-DD* (contoh: 2026-11-20), pilih angka dari daftar rekomendasi (jika ada), atau ketik 0 untuk batal.";
                        $this->sendMessage($waNumber, $reply);
                    }
                }
                break;

            case 'checkout_jam':
                if ($message == '0') {
                    $this->stepSapaan($waNumber, $user, $session);
                } else {
                    $jamList = $sessionData['temp_jam_valid'] ?? [];
                    if (is_numeric($message) && !empty($jamList)) {
                        $index = ((int) $message) - 1;
                        if (isset($jamList[$index])) {
                            $sessionData['co_jam'] = $jamList[$index] . ':00';
                            unset($sessionData['temp_jam_valid']);
                            $session->update(['session_data' => $sessionData]);
                            $this->stepCheckoutMetodeBayar($waNumber, $session);
                            break;
                        }
                    }

                    if (preg_match("/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/", $message) || preg_match("/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/", $message)) {
                        $jamInput = (strlen($message) == 5) ? $message . ':00' : $message;
                        $this->prosesValidasiJam($waNumber, $jamInput, $session);
                    } else {
                        $cfg = $this->getOperationalSettings();
                        $reply = $this->getTemplate('jam_format_salah', [
                            'jam_buka' => $cfg['start_str'],
                            'jam_tutup' => $cfg['end_str'],
                        ]) ?? "Format atau pilihan jam belum sesuai Kak. Ketik jam antara *{$cfg['start_str']} - {$cfg['end_str']}* (contoh: 14:00), pilih angka dari rekomendasi, atau ketik 0 untuk batal.";
                        $this->sendMessage($waNumber, $reply);
                    }
                }
                break;

            case 'checkout_metode_bayar':
                if ($message == '0') {
                    $this->stepSapaan($waNumber, $user, $session);
                } elseif (in_array($message, ['1', '2', '3'])) {
                    $metodeMap = ['1' => 'transfer_full', '2' => 'transfer_dp', '3' => 'cod'];
                    $sessionData['co_metode'] = $metodeMap[$message];
                    $session->update(['session_data' => $sessionData]);
                    $this->stepCheckoutKonfirmasi($waNumber, $user, $session);
                } else {
                    $this->replyInvalid($waNumber);
                }
                break;

            case 'checkout_konfirmasi':
                if ($message == '0' || $message == '2') {
                    $this->stepSapaan($waNumber, $user, $session);
                } elseif ($message == '1') {
                    $this->prosesBikinPesanan($waNumber, $user, $session);
                } else {
                    $this->replyInvalid($waNumber);
                }
                break;

            case 'riwayat_pesanan':
                $this->prosesInteraksiRiwayat($waNumber, $user, $message, $session);
                break;

            case 'batal_pesanan_alasan':
                $this->prosesAlasanBatalPesanan($waNumber, $user, $message, $session);
                break;

            case 'batal_pesanan_refund':
                $this->prosesRefundBatalPesanan($waNumber, $user, $message, $session);
                break;

            default:
                $this->stepSapaan($waNumber, $user, $session);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    // ==========================================
    // HELPER: TEMPLATE & STORE SETTING
    // ==========================================

    private function getTemplate($state, $placeholders = []): ?string
    {
        $template = BotTemplate::where('session_state', $state)->where('is_active', true)->first();
        if (!$template || empty(trim($template->message_text))) {
            return null;
        }

        $text = $template->message_text;
        foreach ($placeholders as $key => $value) {
            $text = str_replace('{' . $key . '}', (string) $value, $text);
        }
        return $text;
    }

    private function getOperationalSettings(): array
    {
        $setting = StoreSetting::first();

        $rawStart = $setting->operational_time_start ?? '08:00:00';
        $rawEnd   = $setting->operational_time_end ?? '17:00:00';

        $startParts = explode(':', $rawStart);
        $endParts   = explode(':', $rawEnd);

        $startHour = (int) ($startParts[0] ?? 8);
        $endHour   = (int) ($endParts[0] ?? 17);

        if ($endHour < $startHour) {
            $endHour = 17;
        }

        $jamSlots = [];
        for ($h = $startHour; $h <= $endHour; $h++) {
            $jamSlots[] = sprintf("%02d:00", $h);
        }

        return [
            'max_qty_per_day'            => (int) ($setting->max_qty_per_day ?? 60),
            'max_orders_per_hour'        => (int) ($setting->max_orders_per_hour ?? 5),
            'start_hour'                 => $startHour,
            'end_hour'                   => $endHour,
            'start_str'                  => sprintf("%02d:00", $startHour),
            'end_str'                    => sprintf("%02d:00", $endHour),
            'jam_slots'                  => $jamSlots,
            'free_delivery_max_distance' => (float) ($setting->free_delivery_max_distance ?? 10),
            'active_couriers'            => $setting->active_couriers ?? 'jne,sicepat,jnt',
        ];
    }

    private function replyInvalid($waNumber)
    {
        $reply = $this->getTemplate('pesan_tidak_valid')
            ?? "Maaf Kak, pilihan tidak valid atau format salah. Silakan ketik angka yang sesuai dengan opsi di atas.\n\nKetik 0 untuk kembali.";
        $this->sendMessage($waNumber, $reply);
    }

    // ==========================================
    // LOGIC MENU UTAMA & KATALOG
    // ==========================================

    private function stepSapaan($waNumber, $user, $session)
    {
        $session->update(['current_step' => 'sapaan']);
        $reply = $this->getTemplate('sapaan', ['nama' => $user->name])
            ?? "Halo Kak {$user->name}! Selamat datang di Raya Kitchen. 🍞✨\n\nKetik angka dari menu di bawah ini untuk mulai:\n1. 📖 Lihat Katalog Produk\n2. 🛒 Cek Keranjang Saya\n3. 📦 Cek Status Pesanan\n4. ℹ️ Cara Pemesanan";
        $this->sendMessage($waNumber, $reply);
    }

    private function stepInstruksi($waNumber, $session)
    {
        $session->update(['current_step' => 'instruksi']);
        $reply = $this->getTemplate('instruksi')
            ?? "Cara pesannya gampang banget Kak!\n\n1. Pilih 'Lihat Katalog' lalu ketik angka produk yang mau dibeli.\n2. Masukkan jumlah produk.\n3. Ikuti langkah pengisian data pengiriman.\n4. Pilih metode pembayaran (Lunas, DP, atau COD).\n5. Lakukan pembayaran via link QRIS dan pesanan otomatis diproses!\n\n0. 🔙 Kembali ke Menu Utama";
        $this->sendMessage($waNumber, $reply);
    }

    private function stepLihatKategori($waNumber, $session)
    {
        $session->update(['current_step' => 'lihat_kategori']);
        $categories = Product::where('is_active', true)->get();

        if ($categories->isEmpty()) {
            $reply = $this->getTemplate('kategori_kosong')
                ?? "Maaf Kak, saat ini belum ada kategori produk yang aktif.\n\n0. 🔙 Kembali ke Menu Utama";
            $this->sendMessage($waNumber, $reply);
            return;
        }

        $daftar = "";
        foreach ($categories as $index => $cat) {
            $daftar .= ($index + 1) . ". {$cat->name}\n";
        }
        $reply = $this->getTemplate('lihat_kategori', ['daftar_kategori' => $daftar])
            ?? "Silakan pilih kategori produk yang mau dilihat:\n\n{$daftar}\n0. 🔙 Kembali ke Menu Utama";
        $this->sendMessage($waNumber, $reply);
    }

    private function prosesPilihKategori($waNumber, $message, $session)
    {
        $categories = Product::where('is_active', true)->get();
        $index = ((int) $message) - 1;

        if (is_numeric($message) && isset($categories[$index])) {
            $this->stepLihatVarian($waNumber, $session, $categories[$index]->id);
        } else {
            $this->replyInvalid($waNumber);
        }
    }

    private function stepLihatVarian($waNumber, $session, $productId)
    {
        $sessionData = $session->session_data ?? [];
        $sessionData['selected_product_id'] = $productId;
        $session->update(['current_step' => 'lihat_varian', 'session_data' => $sessionData]);

        $variants = ProductVariant::where('product_id', $productId)->get();
        $product = Product::find($productId);

        if ($variants->isEmpty()) {
            $reply = $this->getTemplate('varian_kosong', ['nama_kategori' => $product->name ?? '-'])
                ?? "Maaf Kak, belum ada varian produk untuk kategori *{$product->name}*.\n\n0. 🔙 Kembali ke Daftar Kategori";
            $this->sendMessage($waNumber, $reply);
            return;
        }

        $daftar = "";
        foreach ($variants as $index => $var) {
            $harga = number_format($var->price, 0, ',', '.');
            $daftar .= ($index + 1) . ". {$var->variant_name} (Rp {$harga})\n";
        }
        $reply = $this->getTemplate('lihat_varian', [
            'nama_kategori' => $product->name,
            'daftar_varian' => $daftar
        ]) ?? "Kategori: *{$product->name}*\nPilih varian yang mau dilihat detailnya:\n\n{$daftar}\n0. 🔙 Kembali ke Daftar Kategori";

        $this->sendMessage($waNumber, $reply);
    }

    private function prosesPilihVarian($waNumber, $message, $session)
    {
        $productId = $session->session_data['selected_product_id'] ?? null;
        if (!$productId) {
            $this->stepLihatKategori($waNumber, $session);
            return;
        }

        $variants = ProductVariant::where('product_id', $productId)->get();
        $index = ((int) $message) - 1;

        if (is_numeric($message) && isset($variants[$index])) {
            $this->stepDetailVarian($waNumber, $session, $variants[$index]);
        } else {
            $this->replyInvalid($waNumber);
        }
    }

    private function stepDetailVarian($waNumber, $session, $variant)
    {
        $sessionData = $session->session_data ?? [];
        $sessionData['selected_variant_id'] = $variant->id;
        $session->update(['current_step' => 'detail_varian', 'session_data' => $sessionData]);

        $harga = number_format($variant->price, 0, ',', '.');
        $kategori = $variant->product->name ?? '-';
        $deskripsi = $variant->product->description ?? '-';

        // Normalisasi URL Gambar Produk agar bisa dibuka dari WhatsApp
        $appUrl = rtrim(request()->getSchemeAndHttpHost(), '/');
        $rawImg = $variant->image_url;

        if (empty($rawImg)) {
            $linkGambar = '-';
        } elseif (str_starts_with($rawImg, 'http')) {
            $linkGambar = $rawImg;
        } else {
            $cleanPath = ltrim($rawImg, '/');
            // Cek apakah di DB sudah tertulis 'produk/namafile.jpg' atau baru 'namafile.jpg'
            if (str_starts_with($cleanPath, 'produk/')) {
                $linkGambar = "{$appUrl}/storage/{$cleanPath}";
            } else {
                $linkGambar = "{$appUrl}/storage/produk/{$cleanPath}";
            }
        }

        $reply = $this->getTemplate('detail_varian', [
            'nama_produk'      => $variant->variant_name,
            'kategori_produk'  => $kategori,
            'harga_produk'     => $harga,
            'deskripsi_produk' => $deskripsi,
            'link_gambar'      => $linkGambar,
        ]) ?? "Detail Produk:\n\n🍞 *{$kategori} - {$variant->variant_name}*\n🏷️ Kategori: {$kategori}\n💰 Harga: Rp {$harga}\n🖼️ Foto Produk: {$linkGambar}\n\n📝 Deskripsi:\n{$deskripsi}\n\nKetik angka berikut:\n1. 🛒 Tambah ke Keranjang\n0. 🔙 Kembali ke Daftar Varian";

        $this->sendMessage($waNumber, $reply);
    }

    // ==========================================
    // KERANJANG & CHECKOUT DATA DIRI
    // ==========================================

    private function stepInputQty($waNumber, $session)
    {
        $sessionData = $session->session_data ?? [];
        $variantId = $sessionData['selected_variant_id'] ?? null;
        $variant = ProductVariant::with('product')->find($variantId);

        if (!$variant) {
            $this->stepLihatKategori($waNumber, $session);
            return;
        }

        $namaLengkap = ($variant->product->name ?? 'Produk') . ' - ' . $variant->variant_name;
        $session->update(['current_step' => 'input_qty']);

        $reply = $this->getTemplate('input_qty', ['nama_produk' => $namaLengkap])
            ?? "Berapa banyak *{$namaLengkap}* yang mau dipesan? (Ketik angkanya saja, misal: 2)\n\n0. 🔙 Batal & Kembali";
        $this->sendMessage($waNumber, $reply);
    }

    private function prosesMasukKeranjang($waNumber, $user, $qty, $session)
    {
        $variantId = $session->session_data['selected_variant_id'] ?? null;
        if ($variantId) {
            $cart = Cart::where('user_id', $user->id)->where('product_variant_id', $variantId)->first();
            if ($cart) {
                $cart->increment('quantity', $qty);
            } else {
                Cart::create(['user_id' => $user->id, 'product_variant_id' => $variantId, 'quantity' => $qty]);
            }
        }
        $this->stepLihatKeranjang($waNumber, $user, $session);
    }

    private function stepLihatKeranjang($waNumber, $user, $session)
    {
        $carts = Cart::with('productVariant.product')->where('user_id', $user->id)->get();
        if ($carts->isEmpty()) {
            $session->update(['current_step' => 'keranjang']);
            $reply = $this->getTemplate('keranjang_kosong')
                ?? "Keranjang belanja kamu masih kosong nih Kak.\n\nKetik 0 untuk 🔙 Kembali ke Menu Utama.";
            $this->sendMessage($waNumber, $reply);
            return;
        }

        $session->update(['current_step' => 'keranjang']);

        $isi = "";
        $total = 0;
        foreach ($carts as $c) {
            $sub = $c->quantity * $c->productVariant->price;
            $total += $sub;

            $namaProduk = $c->productVariant->product->name ?? 'Produk';
            $namaVarian = $c->productVariant->variant_name;

            $isi .= "- {$namaProduk} - {$namaVarian} ({$c->quantity}x) = Rp " . number_format($sub, 0, ',', '.') . "\n";
        }

        $reply = $this->getTemplate('keranjang', [
            'isi_keranjang' => $isi,
            'total_harga'   => number_format($total, 0, ',', '.')
        ]) ?? "🛒 *Isi Keranjang Kamu:*\n\n{$isi}\nTotal Belanja: Rp " . number_format($total, 0, ',', '.') . "\n\nPilih aksi:\n1. ✅ Lanjut Checkout\n2. ➕ Tambah Produk Lain\n3. 🗑️ Kosongkan Keranjang\n0. 🔙 Kembali ke Menu Utama";

        $this->sendMessage($waNumber, $reply);
    }

    private function stepCheckoutNama($waNumber, $session)
    {
        $session->update(['current_step' => 'checkout_nama']);
        $reply = $this->getTemplate('checkout_nama')
            ?? "Mulai proses checkout ya!\n\nAtas nama siapa pesanan ini? (Ketik nama penerima)\n\n0. 🔙 Batal Checkout";
        $this->sendMessage($waNumber, $reply);
    }

    // ==========================================
    // ALAMAT & PARSING FORM ALAMAT BARU
    // ==========================================

    private function getFormAlamatText(): string
    {
        return "--- Form Alamat Baru ---\n"
             . "Label Alamat : \n"
             . "Provinsi : \n"
             . "Kota / Kabupaten : \n"
             . "Kecamatan : \n"
             . "Kode Pos : \n"
             . "Alamat Jalan : \n"
             . "Patokan : ";
    }

    private function stepCheckoutAlamat($waNumber, $user, $session)
    {
        $session->update(['current_step' => 'checkout_alamat']);
        $addresses = UserAddress::where('user_id', $user->id)->get();
        $formBaru = $this->getFormAlamatText();

        if ($addresses->isEmpty()) {
            $reply = $this->getTemplate('checkout_alamat_kosong', ['form_alamat' => $formBaru])
                ?? "Kak, alamat pengiriman kamu masih kosong nih.\nSilakan *copy-paste* dan isi form di bawah ini lalu kirim balik ke sini ya:\n\n{$formBaru}\n\n0. 🔙 Batal Checkout";
            $this->sendMessage($waNumber, $reply);
            return;
        }

        $daftar = "Alamat Tersimpan:\n";
        foreach ($addresses as $idx => $addr) {
            $patokan = $addr->landmark ? " (Patokan: {$addr->landmark})" : "";
            $daftar .= ($idx + 1) . ". [{$addr->label}] {$addr->street_address}, {$addr->district}, {$addr->city}{$patokan}\n";
        }

        $reply = $this->getTemplate('checkout_alamat', [
            'daftar_alamat' => $daftar,
            'form_alamat'   => $formBaru
        ]) ?? "Kirim ke alamat mana Kak?\n\n{$daftar}\nKetik *angka* dari list alamat di atas.\nAtau kalau mau buat alamat baru, *copy-paste* & isi form ini:\n\n{$formBaru}\n\n0. 🔙 Batal Checkout";

        $this->sendMessage($waNumber, $reply);
    }

    private function prosesInputAlamatBaru($waNumber, $user, $message, $session)
    {
        $lines = explode("\n", $message);
        $dataAlamat = [];

        foreach ($lines as $line) {
            if (str_contains($line, ':')) {
                $parts = explode(':', $line, 2);
                $key = strtolower(trim($parts[0]));
                $value = trim($parts[1]);

                if (str_contains($key, 'label')) $dataAlamat['label'] = $value;
                if (str_contains($key, 'provinsi')) $dataAlamat['province'] = $value;
                if (str_contains($key, 'kota') || str_contains($key, 'kabupaten')) $dataAlamat['city'] = $value;
                if (str_contains($key, 'kecamatan')) $dataAlamat['district'] = $value;
                if (str_contains($key, 'kode pos')) $dataAlamat['postal_code'] = $value;
                if (str_contains($key, 'jalan')) $dataAlamat['street_address'] = $value;
                if (str_contains($key, 'patokan')) $dataAlamat['landmark'] = $value;
            }
        }

        if (empty($dataAlamat['province']) || empty($dataAlamat['city']) || empty($dataAlamat['district']) || empty($dataAlamat['street_address'])) {
            $formBaru = $this->getFormAlamatText();
            $reply = $this->getTemplate('form_alamat_tidak_lengkap', ['form_alamat' => $formBaru])
                ?? "Format form alamat belum lengkap Kak. Pastikan *Provinsi, Kota/Kabupaten, Kecamatan, dan Alamat Jalan* sudah terisi setelah tanda titik dua (:).\n\nSilakan copy dan isi ulang form berikut:\n\n{$formBaru}\n\n0. 🔙 Batal Checkout";
            $this->sendMessage($waNumber, $reply);
            return;
        }

        UserAddress::where('user_id', $user->id)->update(['is_primary' => 0]);

        $newAddress = UserAddress::create([
            'user_id'                   => $user->id,
            'label'                     => !empty($dataAlamat['label']) ? $dataAlamat['label'] : 'Rumah',
            'recipient_name'            => $session->session_data['co_nama'] ?? $user->name,
            'recipient_whatsapp'        => $waNumber,
            'province'                  => $dataAlamat['province'],
            'city'                      => $dataAlamat['city'],
            'district'                  => $dataAlamat['district'],
            'postal_code'               => !empty($dataAlamat['postal_code']) ? $dataAlamat['postal_code'] : '00000',
            'street_address'            => $dataAlamat['street_address'],
            'landmark'                  => !empty($dataAlamat['landmark']) ? $dataAlamat['landmark'] : null,
            'is_primary'                => 1,
            'latitude'                  => null,
            'longitude'                 => null,
            'distance_to_store'         => null,
            'rajaongkir_destination_id' => null,
        ]);

        $appUrl = rtrim(request()->getSchemeAndHttpHost(), '/');
        $linkMaps = $appUrl . "/pin-lokasi/" . $newAddress->id . "?wa=" . $waNumber;

        $reply = $this->getTemplate('alamat_tersimpan_pin_maps', ['link_maps' => $linkMaps])
            ?? "✅ Alamat berhasil disimpan dan dijadikan alamat utama!\n\nLangkah terakhir, silakan klik link di bawah ini untuk menentukan titik akurat di Maps agar sistem bisa menghitung ongkos kirimnya:\n{$linkMaps}";

        $this->sendMessage($waNumber, $reply);
    }

    // ==========================================
    // LOGIC PERCABANGAN ONGKIR (PANGGIL CHECKOUT CONTROLLER)
    // ==========================================

    private function stepCekOngkirDanJadwal($waNumber, $user, $session)
    {
        $sd = $session->session_data;

        $notifLoading = $this->getTemplate('loading_cek_ongkir')
            ?? "Tunggu sebentar ya Kak, sistem sedang mengecek jarak & tarif pengiriman ke alamatmu... 🚚";
        $this->sendMessage($waNumber, $notifLoading);

        $req = Request::create('/api/pembeli/checkout/cek-ongkir', 'POST', [
            'address_id' => $sd['co_address_id']
        ]);
        $req->setUserResolver(fn() => $user);

        $response = app(CheckoutController::class)->cekOngkir($req);
        $resData = $response->getData(true);

        if (($resData['status'] ?? 'error') === 'success') {
            if (!empty($resData['is_internal'])) {
                $sd['co_is_internal'] = true;
                $sd['co_shipping_cost'] = $resData['shipping_cost'] ?? 0;
                $sd['co_courier_name'] = $resData['courier_name'] ?? 'Kurir Internal Raya Kitchen';
                $sd['co_shipping_service'] = $resData['shipping_service'] ?? 'Gratis Ongkir';

                $session->update(['session_data' => $sd]);

                $notifGratis = $this->getTemplate('info_gratis_ongkir', ['jarak' => $sd['co_distance'] ?? 0])
                    ?? "Asyik! Jarak lokasi kamu ({$sd['co_distance']} KM) masuk area *Gratis Ongkir* Kurir Internal Raya Kitchen. Yuk lanjut pilih jadwal pengirimannya!";
                $this->sendMessage($waNumber, $notifGratis);

                $this->stepCheckoutTanggal($waNumber, $session);
            } else {
                $rates = $resData['rates'] ?? [];
                if (empty($rates)) {
                    $reply = $this->getTemplate('ekspedisi_tidak_tersedia')
                        ?? "Maaf Kak, layanan kurir ekspedisi belum tersedia untuk tujuan alamatmu.\n\n0. 🔙 Kembali ke Menu Utama";
                    $this->sendMessage($waNumber, $reply);
                    $this->stepSapaan($waNumber, $user, $session);
                    return;
                }

                $daftarKurir = "";
                foreach ($rates as $idx => $r) {
                    $harga = number_format($r['price'], 0, ',', '.');
                    $daftarKurir .= ($idx + 1) . ". {$r['company']} - {$r['type']} (Rp {$harga}) | Est: {$r['duration']} hr\n";
                }

                $sd['temp_rates'] = $rates;
                $session->update([
                    'current_step' => 'checkout_pilih_ekspedisi',
                    'session_data' => $sd
                ]);

                $reply = $this->getTemplate('checkout_pilih_ekspedisi', ['daftar_ekspedisi' => $daftarKurir])
                    ?? "Alamatmu berada di luar jangkauan kurir internal. Silakan pilih layanan ekspedisi berikut Kak:\n\n{$daftarKurir}\nBalas dengan *angka pilihanmu*.\n\n0. 🔙 Batal Checkout";
                $this->sendMessage($waNumber, $reply);
            }
        } else {
            $reply = $this->getTemplate('ongkir_gangguan')
                ?? "Sistem pengecekan ongkir sedang sibuk Kak. Silakan tunggu beberapa saat lalu pilih menu Keranjang & Checkout kembali, atau lanjutkan pemesanan via Website ya. 🙏\n\n0. 🔙 Kembali ke Menu Utama";
            $this->sendMessage($waNumber, $reply);
            $this->stepSapaan($waNumber, $user, $session);
        }
    }

    // ==========================================
    // VALIDASI TANGGAL & JAM
    // ==========================================

    private function stepCheckoutTanggal($waNumber, $session)
    {
        $session->update(['current_step' => 'checkout_tanggal']);
        $contohTgl = now()->addDays(2)->format('Y-m-d');

        $reply = $this->getTemplate('checkout_tanggal', ['contoh_tanggal' => $contohTgl])
            ?? "Kapan pesanan mau dikirim/diambil?\n(Ketik dengan format *YYYY-MM-DD*, contoh: {$contohTgl})\n*Minimal pemesanan H+1 dari hari ini*\n\n0. 🔙 Batal Checkout";
        $this->sendMessage($waNumber, $reply);
    }

    private function prosesValidasiTanggal($waNumber, $user, $inputTanggal, $session)
    {
        $cfg = $this->getOperationalSettings();
        $maxQtyPerDay = $cfg['max_qty_per_day'];
        $cartQty = (int) Cart::where('user_id', $user->id)->sum('quantity');

        try {
            $tanggalPilihan = Carbon::createFromFormat('Y-m-d', $inputTanggal)->startOfDay();
        } catch (\Exception $e) {
            $this->replyInvalid($waNumber);
            return;
        }

        $besok = now()->addDay()->startOfDay();

        if ($tanggalPilihan->lt($besok)) {
            $alasan = $this->getTemplate('tanggal_tolak_minimal')
                ?? "Maaf Kak, tanggal pengiriman minimal H+1 (besok) dari hari pemesanan.";
            $this->kirimRekomendasiTanggal($waNumber, $user, $session, $besok, $alasan);
            return;
        }

        $tglStr = $tanggalPilihan->format('Y-m-d');

        if (StoreHoliday::whereDate('holiday_date', $tglStr)->exists()) {
            $alasan = $this->getTemplate('tanggal_tolak_libur', ['tanggal' => $tglStr])
                ?? "Maaf Kak, pada tanggal *{$tglStr}* dapur Raya Kitchen sedang libur operasional.";
            $this->kirimRekomendasiTanggal($waNumber, $user, $session, $tanggalPilihan, $alasan);
            return;
        }

        $terisi = (int) OrderItem::whereHas('order', function ($q) use ($tglStr) {
            $q->whereDate('delivery_date', $tglStr)->where('status', '!=', 'dibatalkan');
        })->sum('quantity');

        if (($terisi + $cartQty) > $maxQtyPerDay) {
            $sisaKuota = max(0, $maxQtyPerDay - $terisi);
            $alasan = $this->getTemplate('tanggal_tolak_penuh', [
                'tanggal'    => $tglStr,
                'sisa_kuota' => $sisaKuota
            ]) ?? "Waduh, kuota produksi dapur pada tanggal *{$tglStr}* sudah penuh untuk jumlah pesananmu (Sisa kuota: {$sisaKuota} pcs).";
            $this->kirimRekomendasiTanggal($waNumber, $user, $session, $tanggalPilihan, $alasan);
            return;
        }

        $sessionData = $session->session_data;
        $sessionData['co_tanggal'] = $tglStr;
        unset($sessionData['temp_tanggal_valid']);
        $session->update(['session_data' => $sessionData]);

        $this->stepCheckoutJam($waNumber, $session, true);
    }

    private function kirimRekomendasiTanggal($waNumber, $user, $session, Carbon $targetDate, string $alasanPenolakan)
    {
        $cfg = $this->getOperationalSettings();
        $maxQtyPerDay = $cfg['max_qty_per_day'];
        $cartQty = (int) Cart::where('user_id', $user->id)->sum('quantity');
        $besok = now()->addDay()->startOfDay();

        $startCheck = $targetDate->copy()->subDays(3);
        if ($startCheck->lt($besok)) {
            $startCheck = $besok->copy();
        }

        $validDates = [];
        $cursor = $startCheck->copy();
        $maxScanDays = 30;
        $scanned = 0;

        while (count($validDates) < 5 && $scanned < $maxScanDays) {
            $tglStr = $cursor->format('Y-m-d');
            $isLibur = StoreHoliday::whereDate('holiday_date', $tglStr)->exists();

            if (!$isLibur) {
                $terisi = (int) OrderItem::whereHas('order', function ($q) use ($tglStr) {
                    $q->whereDate('delivery_date', $tglStr)->where('status', '!=', 'dibatalkan');
                })->sum('quantity');

                if (($terisi + $cartQty) <= $maxQtyPerDay) {
                    $validDates[] = $tglStr;
                }
            }
            $cursor->addDay();
            $scanned++;
        }

        $sessionData = $session->session_data;
        $sessionData['temp_tanggal_valid'] = $validDates;
        $session->update(['session_data' => $sessionData]);

        $daftar = "";
        foreach ($validDates as $idx => $tgl) {
            $formatted = Carbon::parse($tgl)->locale('id')->isoFormat('dddd, D MMMM YYYY');
            $daftar .= ($idx + 1) . ". {$formatted} ({$tgl})\n";
        }

        $reply = $this->getTemplate('rekomendasi_tanggal', [
            'alasan_penolakan' => $alasanPenolakan,
            'daftar_tanggal'   => $daftar
        ]) ?? "{$alasanPenolakan}\n\nBerikut rekomendasi tanggal terdekat yang kuotanya masih muat untuk pesananmu Kak:\n\n{$daftar}\nBalas dengan *angka pilihan* di atas, atau ketik manual *tanggal lain (YYYY-MM-DD)*.\n\n0. 🔙 Batal Checkout";

        $this->sendMessage($waNumber, $reply);
    }

    private function stepCheckoutJam($waNumber, $session, $firstTime = false)
    {
        $session->update(['current_step' => 'checkout_jam']);
        if ($firstTime) {
            $cfg = $this->getOperationalSettings();
            $reply = $this->getTemplate('checkout_jam', [
                'jam_buka'  => $cfg['start_str'],
                'jam_tutup' => $cfg['end_str']
            ]) ?? "Jam berapa pesanan mau dikirim/diambil Kak?\n(Jam operasional kurir: *{$cfg['start_str']} sampai {$cfg['end_str']} WIB*, ketik contoh: 14:00)\n\n0. 🔙 Batal Checkout";

            $this->sendMessage($waNumber, $reply);
        }
    }

    private function prosesValidasiJam($waNumber, $inputJam, $session)
    {
        $sd = $session->session_data;
        $tanggalStr = $sd['co_tanggal'];
        $cfg = $this->getOperationalSettings();

        $parts = explode(':', $inputJam);
        $jamUserInt = (int) ($parts[0] ?? -1);
        $jamSlotStr = sprintf("%02d:00", $jamUserInt);

        if (!in_array($jamSlotStr, $cfg['jam_slots'])) {
            $alasan = $this->getTemplate('jam_tolak_operasional', [
                'jam_buka'  => $cfg['start_str'],
                'jam_tutup' => $cfg['end_str']
            ]) ?? "Maaf Kak, jam yang dipilih berada di luar jam operasional kurir kami (*{$cfg['start_str']} - {$cfg['end_str']} WIB*).";

            $this->kirimRekomendasiJam($waNumber, $session, $tanggalStr, $alasan);
            return;
        }

        $terisiJamIni = Order::whereDate('delivery_date', $tanggalStr)
            ->where('status', '!=', 'dibatalkan')
            ->whereRaw("SUBSTRING(delivery_time, 1, 2) = ?", [sprintf("%02d", $jamUserInt)])
            ->count();

        if ($terisiJamIni >= $cfg['max_orders_per_hour']) {
            $alasan = $this->getTemplate('jam_tolak_penuh', ['jam' => $jamSlotStr])
                ?? "Waduh, jadwal pengiriman pada jam *{$jamSlotStr} WIB* sudah penuh Kak (Maksimal {$cfg['max_orders_per_hour']} antrean per jam).";

            $this->kirimRekomendasiJam($waNumber, $session, $tanggalStr, $alasan);
            return;
        }

        $sessionData = $session->session_data;
        $sessionData['co_jam'] = $jamSlotStr . ':00';
        unset($sessionData['temp_jam_valid']);
        $session->update(['session_data' => $sessionData]);

        $this->stepCheckoutMetodeBayar($waNumber, $session);
    }

    private function kirimRekomendasiJam($waNumber, $session, string $tanggalStr, string $alasanPenolakan)
    {
        $cfg = $this->getOperationalSettings();
        $maxOrdersPerHour = $cfg['max_orders_per_hour'];

        $orders = Order::whereDate('delivery_date', $tanggalStr)
            ->where('status', '!=', 'dibatalkan')
            ->get();

        $rekapJam = [];
        foreach ($orders as $order) {
            $jamParts = explode(':', $order->delivery_time);
            $jamKey = sprintf("%02d:00", (int) ($jamParts[0] ?? 0));
            $rekapJam[$jamKey] = ($rekapJam[$jamKey] ?? 0) + 1;
        }

        $validTimes = [];
        foreach ($cfg['jam_slots'] as $slot) {
            $terisi = $rekapJam[$slot] ?? 0;
            if ($terisi < $maxOrdersPerHour) {
                $validTimes[] = $slot;
            }
        }

        if (empty($validTimes)) {
            $session->update(['current_step' => 'checkout_tanggal']);
            $user = User::where('whatsapp_number', $waNumber)->first();
            $alasanFull = $alasanPenolakan . "\nSayangnya seluruh slot jam pada tanggal *{$tanggalStr}* sudah penuh.";
            $this->kirimRekomendasiTanggal($waNumber, $user, $session, Carbon::parse($tanggalStr), $alasanFull);
            return;
        }

        $sessionData = $session->session_data;
        $sessionData['temp_jam_valid'] = $validTimes;
        $session->update(['session_data' => $sessionData]);

        $daftar = "";
        foreach ($validTimes as $idx => $j) {
            $sisaSlot = $maxOrdersPerHour - ($rekapJam[$j] ?? 0);
            $daftar .= ($idx + 1) . ". Jam {$j} WIB (Sisa {$sisaSlot} slot)\n";
        }

        $reply = $this->getTemplate('rekomendasi_jam', [
            'alasan_penolakan' => $alasanPenolakan,
            'tanggal'          => $tanggalStr,
            'daftar_jam'       => $daftar
        ]) ?? "{$alasanPenolakan}\n\nBerikut jam operasional yang masih kosong di tanggal *{$tanggalStr}*:\n\n{$daftar}\nBalas dengan *angka pilihan*, ketik manual *jam lain (HH:MM)*, atau ketik 0 untuk batal.";

        $this->sendMessage($waNumber, $reply);
    }

    // ==========================================
    // METODE BAYAR, KONFIRMASI & PROSES PESANAN
    // ==========================================

    private function stepCheckoutMetodeBayar($waNumber, $session)
    {
        $session->update(['current_step' => 'checkout_metode_bayar']);
        $reply = $this->getTemplate('checkout_metode_bayar')
            ?? "Pilih skema pembayaran:\n\n1. 💳 Bayar Lunas (100% via QRIS)\n2. ⏳ DP Dulu (30% via QRIS, sisa via sistem)\n3. 🤝 COD (Bayar di tempat, wajib transfer DP 30%)\n\n0. 🔙 Batal Checkout";
        $this->sendMessage($waNumber, $reply);
    }

    private function stepCheckoutKonfirmasi($waNumber, $user, $session)
    {
        $session->update(['current_step' => 'checkout_konfirmasi']);
        $sd = $session->session_data;

        $carts = Cart::with('productVariant.product')->where('user_id', $user->id)->get();
        $totalHargaBarang = 0;

        $rincian = "Pemesan: {$sd['co_nama']}\nAlamat: {$sd['co_alamat']}\n";

        if ($sd['co_is_internal']) {
            $jamFormatted = substr($sd['co_jam'], 0, 5);
            $rincian .= "Kurir: {$sd['co_courier_name']} (Gratis)\n";
            $rincian .= "Jadwal: {$sd['co_tanggal']} Jam {$jamFormatted} WIB\n\nItem:\n";
        } else {
            $rincian .= "Ekspedisi: {$sd['co_courier_name']} - Rp " . number_format($sd['co_shipping_cost'], 0, ',', '.') . "\n";
            $rincian .= "Layanan: {$sd['co_shipping_service']}\n\nItem:\n";
        }

        foreach ($carts as $c) {
            $sub = $c->quantity * $c->productVariant->price;
            $totalHargaBarang += $sub;

            $namaProduk = $c->productVariant->product->name ?? 'Produk';
            $namaVarian = $c->productVariant->variant_name;

            $rincian .= "- {$namaProduk} - {$namaVarian} ({$c->quantity}x) = Rp " . number_format($sub, 0, ',', '.') . "\n";
        }

        $grandTotal = $totalHargaBarang + $sd['co_shipping_cost'];
        $rincian .= "\nTotal Pesanan: Rp " . number_format($grandTotal, 0, ',', '.');

        $tagihan = 0;
        if ($sd['co_metode'] == 'transfer_full') {
            $tagihan = $grandTotal;
            $rincian .= "\nMetode: Bayar Lunas (QRIS)";
        } else {
            $tagihan = (int) round($grandTotal * 0.3);
            $met = $sd['co_metode'] == 'cod' ? 'COD (Wajib DP 30%)' : 'DP 30% (QRIS)';
            $rincian .= "\nMetode: {$met}";
        }

        $reply = $this->getTemplate('checkout_konfirmasi', [
            'ringkasan_pesanan' => $rincian,
            'total_tagihan'     => number_format($tagihan, 0, ',', '.')
        ]) ?? "🧾 *Ringkasan Pesanan*\n{$rincian}\n\n*Tagihan Saat Ini: Rp " . number_format($tagihan, 0, ',', '.') . "*\n\nApakah data pesanan sudah benar?\n1. ✅ Ya, Buat Pesanan & Bayar\n2. ❌ Tidak, Batal Checkout";

        $this->sendMessage($waNumber, $reply);
    }

    private function prosesBikinPesanan($waNumber, $user, $session)
    {
        $sd = $session->session_data;

        $req = Request::create('/api/pembeli/checkout/process', 'POST', [
            'name'             => $sd['co_nama'],
            'phone'            => $waNumber,
            'address'          => $sd['co_alamat'],
            'delivery_date'    => $sd['co_tanggal'],
            'delivery_time'    => $sd['co_jam'],
            'payment_method'   => $sd['co_metode'],
            'shipping_cost'    => $sd['co_shipping_cost'],
            'courier_name'     => $sd['co_courier_name'],
            'shipping_service' => $sd['co_shipping_service'],
            'order_source'     => 'whatsapp',
        ]);
        $req->setUserResolver(fn() => $user);

        $response = app(CheckoutController::class)->store($req);
        $resData = $response->getData(true);

        if (($resData['status'] ?? 'error') === 'success') {
            $orderNumber = $resData['data']['order_number'];
            $amountToPay = $resData['data']['amount_to_pay'] ?? 0;

            $appUrl = rtrim(request()->getSchemeAndHttpHost(), '/');
            $linkBayar = $appUrl . "/pembayaran/" . $orderNumber;
            $linkNota  = $appUrl . "/nota?order_number=" . $orderNumber;

            $session->update(['current_step' => 'sapaan', 'session_data' => []]);

            $reply = $this->getTemplate('pembayaran_qris', [
                'order_number'    => $orderNumber,
                'total_tagihan'   => number_format($amountToPay, 0, ',', '.'),
                'link_pembayaran' => $linkBayar,
                'link_nota'       => $linkNota
            ]) ?? "🎉 Pesanan *#{$orderNumber}* berhasil dibuat!\n\nTotal Tagihan Saat Ini: *Rp " . number_format($amountToPay, 0, ',', '.') . "*\n\nSilakan klik link di bawah ini untuk membayar via QRIS:\n👉 {$linkBayar}\n\nLihat Nota Digital:\n🧾 {$linkNota}\n\nKetik 0 untuk kembali ke Menu Utama.";

            $this->sendMessage($waNumber, $reply);
        } else {
            $reply = $this->getTemplate('pembayaran_gangguan')
                ?? "Maaf Kak, terjadi kendala saat memproses pesananmu. Silakan coba lagi ya.";
            $this->sendMessage($waNumber, $reply);
            $this->stepSapaan($waNumber, $user, $session);
        }
    }

    // ==========================================
    // RIWAYAT PESANAN (PAGINATION, FILTER, BAYAR ULANG & BATAL PESANAN)
    // ==========================================

    private function getFilteredOrders(User $user, string $filterType = 'semua'): array
    {
        $req = Request::create('/api/pembeli/riwayat-pesanan-data', 'GET');
        $req->setUserResolver(fn() => $user);

        $response = app(CheckoutController::class)->index($req);
        $resData = $response->getData(true);
        $allOrders = $resData['data'] ?? [];

        return array_values(array_filter($allOrders, function ($order) use ($filterType) {
            if ($filterType === 'semua') return true;

            $pStatus = strtolower($order['payment_status'] ?? '');
            $oStatus = strtolower($order['raw_status'] ?? strtolower(str_replace(' ', '_', $order['status'] ?? '')));

            if ($filterType === 'unpaid') return ($pStatus === 'unpaid' || $pStatus === 'failed') && $oStatus !== 'dibatalkan';
            if ($filterType === 'paid_dp') return $pStatus === 'paid_dp';
            if ($filterType === 'paid_full') return $pStatus === 'paid_full';
            if ($filterType === 'pending') return $oStatus === 'pending' && $pStatus !== 'unpaid' && $pStatus !== 'failed';
            if ($filterType === 'diproses') return $oStatus === 'diproses';
            if ($filterType === 'siap_diambil') return $oStatus === 'siap_diambil';
            if ($filterType === 'selesai') return $oStatus === 'selesai';
            if ($filterType === 'dibatalkan') return $oStatus === 'dibatalkan';

            return true;
        }));
    }

    private function stepRiwayatPesanan($waNumber, $user, $session, string $filter = 'semua', int $page = 1)
    {
        $filteredOrders = $this->getFilteredOrders($user, $filter);
        $perPage = 5;
        $totalOrders = count($filteredOrders);
        $totalPages = max(1, (int) ceil($totalOrders / $perPage));

        if ($page < 1) $page = 1;
        if ($page > $totalPages) $page = $totalPages;

        $offset = ($page - 1) * $perPage;
        $pagedOrders = array_slice($filteredOrders, $offset, $perPage);

        $orderMap = [];
        foreach ($pagedOrders as $idx => $o) {
            $nomorUrut = $offset + $idx + 1;
            $orderMap[$nomorUrut] = $o['order_number'];
            $orderMap[$idx + 1]   = $o['order_number'];
        }

        $sessionData = is_array($session->session_data) ? $session->session_data : [];
        $sessionData['riwayat_filter'] = $filter;
        $sessionData['riwayat_page']   = $page;
        $sessionData['riwayat_map']    = $orderMap;

        $session->update([
            'current_step' => 'riwayat_pesanan',
            'session_data' => $sessionData
        ]);

        $labelFilterMap = [
            'semua'        => 'SEMUA PESANAN',
            'unpaid'       => 'BELUM DIBAYAR',
            'paid_dp'      => 'SUDAH DP (30%)',
            'paid_full'    => 'LUNAS',
            'pending'      => 'DALAM ANTREAN (PENDING)',
            'diproses'     => 'SEDANG DIPROSES',
            'siap_diambil' => 'SIAP DIANTAR/DIAMBIL',
            'selesai'      => 'SELESAI',
            'dibatalkan'   => 'DIBATALKAN'
        ];
        $labelFilter = $labelFilterMap[$filter] ?? strtoupper($filter);

        if (empty($pagedOrders)) {
            $reply = $this->getTemplate('riwayat_pesanan_kosong', ['label_filter' => $labelFilter])
                ?? "📦 *Filter: {$labelFilter}*\n\nBelum ada pesanan di kategori ini Kak.\n\n🔍 *Ketik Filter Lain:*\n`semua` | `unpaid` | `dp` | `lunas` | `pending` | `diproses` | `siap dikirim` | `selesai` | `batal`\n\n0. 🔙 Kembali ke Menu Utama";
            $this->sendMessage($waNumber, $reply);
            return;
        }

        $appUrl = rtrim(request()->getSchemeAndHttpHost(), '/');
        $daftar = "";

        foreach ($pagedOrders as $idx => $o) {
            $nomor = $offset + $idx + 1;
            $pStatus = strtoupper($o['payment_status']);
            $metode = strtoupper($o['payment_method']);

            $daftar .= "*{$nomor}. #{$o['order_number']}*\n";
            $daftar .= "📅 Tgl: {$o['date']}\n";
            $daftar .= "📌 Status: *{$o['display_status']}* ({$metode} - {$o['tipe_bayar']})\n";
            $daftar .= "💳 Status Bayar: *{$pStatus}*\n";
            $daftar .= "💰 Total: {$o['total_amount_formatted']}\n";

            if ($o['raw_status'] === 'dibatalkan') {
                $daftar .= "🧾 Detail Batal: {$appUrl}/nota?order_number={$o['order_number']}\n";
            } elseif ($o['payment_status'] === 'unpaid' || $o['payment_status'] === 'failed') {
                $daftar .= "👉 Link Bayar QRIS: {$appUrl}/pembayaran/{$o['order_number']}\n";
            } else {
                if ($o['payment_status'] === 'paid_dp' && $o['remaining_amount'] > 0) {
                    $daftar .= "⏳ Sisa Tagihan: *{$o['sisa_tagihan_formatted']}*\n";
                }
                $daftar .= "🧾 Lihat Nota: {$appUrl}/nota?order_number={$o['order_number']}\n";
            }
            $daftar .= "\n";
        }

        $navigasi = "";
        if ($page < $totalPages) {
            $navigasi .= "⏭️ Ketik *lanjut* untuk melihat 5 pesanan berikutnya\n";
        }
        if ($page > 1) {
            $navigasi .= "⏮️ Ketik *prev* untuk halaman sebelumnya\n";
        }

        $reply = $this->getTemplate('riwayat_pesanan', [
            'label_filter'     => $labelFilter,
            'halaman_sekarang' => $page,
            'total_halaman'    => $totalPages,
            'total_pesanan'    => $totalOrders,
            'daftar_pesanan'   => trim($daftar),
            'navigasi_halaman' => $navigasi
        ]) ?? "📦 *Filter: {$labelFilter}*\n📄 *Halaman {$page} dari {$totalPages}* (Total: {$totalOrders} pesanan)\n\n"
            . trim($daftar) . "\n\n──────────────────\n"
            . $navigasi
            . "🔍 *Filter Status:* ketik `semua`, `unpaid`, `dp`, `lunas`, `pending`, `diproses`, `siap dikirim`, `selesai`, atau `batal`\n"
            . "💳 *Bayar Ulang / Pelunasan Sisa DP:* ketik `bayar [nomor]` (contoh: `bayar 1`)\n"
            . "❌ *Batalkan Pesanan Pending:* ketik `batalkan [nomor]` (contoh: `batalkan 1`)\n"
            . "0. 🔙 Kembali ke Menu Utama";

        $this->sendMessage($waNumber, $reply);
    }

    private function prosesInteraksiRiwayat($waNumber, $user, $message, $session)
    {
        $sd = is_array($session->session_data) ? $session->session_data : [];
        $currentFilter = $sd['riwayat_filter'] ?? 'semua';
        $currentPage   = (int) ($sd['riwayat_page'] ?? 1);
        $orderMap      = $sd['riwayat_map'] ?? [];

        $msgLower = strtolower(trim($message));

        if ($msgLower === '0') {
            $this->stepSapaan($waNumber, $user, $session);
            return;
        }

        if (in_array($msgLower, ['lanjut', 'next', 'n'])) {
            $this->stepRiwayatPesanan($waNumber, $user, $session, $currentFilter, $currentPage + 1);
            return;
        }
        if (in_array($msgLower, ['sebelumnya', 'prev', 'p', 'back'])) {
            $this->stepRiwayatPesanan($waNumber, $user, $session, $currentFilter, max(1, $currentPage - 1));
            return;
        }

        $filterAliases = [
            'semua'        => 'semua',
            'all'          => 'semua',
            'unpaid'       => 'unpaid',
            'belum bayar'  => 'unpaid',
            'dp'           => 'paid_dp',
            'paid_dp'      => 'paid_dp',
            'sudah dp'     => 'paid_dp',
            'lunas'        => 'paid_full',
            'paid_full'    => 'paid_full',
            'pending'      => 'pending',
            'antrean'      => 'pending',
            'diproses'     => 'diproses',
            'proses'       => 'diproses',
            'siap'         => 'siap_diambil',
            'siap_diambil' => 'siap_diambil',
            'siap diambil' => 'siap_diambil',
            'siap dikirim' => 'siap_diambil',
            'siap_dikirim' => 'siap_diambil',
            'siap diantar' => 'siap_diambil',
            'siap_diantar' => 'siap_diambil',
            'dikirim'      => 'siap_diambil',
            'diantar'      => 'siap_diambil',
            'selesai'      => 'selesai',
            'batal'        => 'dibatalkan',
            'dibatalkan'   => 'dibatalkan',
        ];

        if (isset($filterAliases[$msgLower])) {
            $this->stepRiwayatPesanan($waNumber, $user, $session, $filterAliases[$msgLower], 1);
            return;
        }

        // Perintah Bayar Ulang / Pelunasan DP
        if (preg_match('/^(bayar|lunasi)\s+(.+)$/i', $message, $matches)) {
            $targetKey = trim($matches[2]);
            $orderNumber = $orderMap[$targetKey] ?? strtoupper($targetKey);

            $req = Request::create('/api/pembeli/checkout/pay-remaining', 'POST', [
                'order_number' => $orderNumber
            ]);
            $req->setUserResolver(fn() => $user);

            $res = app(CheckoutController::class)->payRemaining($req);
            $resData = $res->getData(true);

            if (($resData['status'] ?? 'error') === 'success') {
                $linkBayar = $resData['data']['redirect_url'] ?? url('/pembayaran/' . $orderNumber);
                $tagihan   = number_format($resData['data']['amount_to_pay'] ?? 0, 0, ',', '.');

                $reply = $this->getTemplate('riwayat_bayar_ulang', [
                    'order_number'    => $orderNumber,
                    'total_tagihan'   => $tagihan,
                    'link_pembayaran' => $linkBayar
                ]) ?? "💳 *Pembayaran Pesanan #{$orderNumber}*\nTagihan: *Rp {$tagihan}*\n\nSilakan klik link berikut untuk scan QRIS:\n👉 {$linkBayar}\n\nKetik *0* untuk kembali ke Menu Utama, atau ketik filter/navigasi untuk lanjut cek pesanan.";

                $this->sendMessage($waNumber, $reply);
            } else {
                $this->sendMessage($waNumber, "⚠️ " . ($resData['message'] ?? 'Gagal memproses pembayaran pesanan tersebut.'));
            }
            return;
        }

        // Perintah Batalkan Pesanan
        if (preg_match('/^(batal|batalkan)\s+(.+)$/i', $message, $matches)) {
            $targetKey = trim($matches[2]);
            $orderNumber = $orderMap[$targetKey] ?? strtoupper($targetKey);

            $order = Order::with('transaction')
                ->where('order_number', $orderNumber)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                $reply = $this->getTemplate('batal_tidak_ditemukan', ['order_number' => $orderNumber])
                    ?? "⚠️ Pesanan *#{$orderNumber}* tidak ditemukan Kak.";
                $this->sendMessage($waNumber, $reply);
                return;
            }

            if ($order->status !== 'pending') {
                $statusUpper = strtoupper($order->status);
                $reply = $this->getTemplate('batal_status_tidak_bisa', [
                    'order_number'   => $orderNumber,
                    'status_pesanan' => $statusUpper
                ]) ?? "⚠️ Maaf Kak, pesanan *#{$orderNumber}* statusnya sudah *{$statusUpper}* sehingga tidak bisa dibatalkan.";
                $this->sendMessage($waNumber, $reply);
                return;
            }

            $pStatus = strtolower($order->transaction->payment_status ?? 'unpaid');
            $isPaid = in_array($pStatus, ['paid_dp', 'paid_full']);

            $sd['cancel_order_number'] = $order->order_number;
            $sd['cancel_is_paid'] = $isPaid;
            $session->update([
                'current_step' => 'batal_pesanan_alasan',
                'session_data' => $sd
            ]);

            $reply = $this->getTemplate('batal_pesanan_alasan', ['order_number' => $order->order_number])
                ?? "⚠️ *Batalkan Pesanan #{$order->order_number}*\n\nPilih alasan pembatalan dengan mengetik angkanya:\n1. Ingin merubah rincian pesanan (alamat/item)\n2. Jadwal pengiriman terlalu lama/tidak sesuai\n3. Berubah pikiran\n\nAtau ketik langsung alasan pembatalanmu.\n0. 🔙 Batal & Kembali ke Daftar Pesanan";

            $this->sendMessage($waNumber, $reply);
            return;
        }

        $reply = $this->getTemplate('riwayat_perintah_salah')
            ?? "Perintah tidak dikenali Kak.\n\n- Ketik *lanjut* / *prev* untuk pindah halaman\n- Ketik filter: `semua`, `unpaid`, `dp`, `lunas`, `pending`, `diproses`, `siap dikirim`, `selesai`, `batal`\n- Ketik `bayar [nomor]` untuk bayar/lunasi\n- Ketik `batalkan [nomor]` untuk membatalkan pesanan\n- Ketik *0* untuk ke Menu Utama.";
        $this->sendMessage($waNumber, $reply);
    }

    private function prosesAlasanBatalPesanan($waNumber, $user, $message, $session)
    {
        $sd = is_array($session->session_data) ? $session->session_data : [];

        if ($message === '0') {
            $this->stepRiwayatPesanan($waNumber, $user, $session, $sd['riwayat_filter'] ?? 'semua', $sd['riwayat_page'] ?? 1);
            return;
        }

        $alasanMap = [
            '1' => 'Ingin merubah rincian pesanan (alamat/item)',
            '2' => 'Jadwal pengiriman terlalu lama/tidak sesuai',
            '3' => 'Berubah pikiran',
        ];

        $reason = $alasanMap[$message] ?? ('Lainnya: ' . $message);
        $sd['cancel_reason'] = $reason;

        if (!empty($sd['cancel_is_paid'])) {
            $session->update([
                'current_step' => 'batal_pesanan_refund',
                'session_data' => $sd
            ]);

            $reply = $this->getTemplate('batal_pesanan_refund', ['order_number' => $sd['cancel_order_number']])
                ?? "💳 *Data Pengembalian Dana (Refund)*\nKarena pesanan *#{$sd['cancel_order_number']}* sudah memiliki pembayaran masuk, silakan ketik data rekening/E-Wallet untuk refund dengan format:\n\n*Nama Bank/E-Wallet - Nomor Rekening - Atas Nama*\nContoh: `BCA - 1234567890 - Budi Santoso`\n\n0. 🔙 Batal & Kembali";
            $this->sendMessage($waNumber, $reply);
            return;
        }

        $this->eksekusiCancelOrderKeController($waNumber, $user, $session, $sd['cancel_order_number'], $reason, null, null, null);
    }

    private function prosesRefundBatalPesanan($waNumber, $user, $message, $session)
    {
        $sd = is_array($session->session_data) ? $session->session_data : [];

        if ($message === '0') {
            $this->stepRiwayatPesanan($waNumber, $user, $session, $sd['riwayat_filter'] ?? 'semua', $sd['riwayat_page'] ?? 1);
            return;
        }

        $parts = array_map('trim', explode('-', $message, 3));
        if (count($parts) < 3 || empty($parts[0]) || empty($parts[1]) || empty($parts[2])) {
            $reply = $this->getTemplate('batal_refund_format_salah')
                ?? "Format rekening belum sesuai Kak. Pastikan dipisah tanda strip (-) menjadi 3 bagian:\n*Nama Bank - Nomor Rekening - Atas Nama*\nContoh: `BCA - 1234567890 - Budi Santoso`\n\nAtau ketik 0 untuk kembali.";
            $this->sendMessage($waNumber, $reply);
            return;
        }

        $this->eksekusiCancelOrderKeController(
            $waNumber,
            $user,
            $session,
            $sd['cancel_order_number'],
            $sd['cancel_reason'] ?? 'Dibatalkan oleh pelanggan via WhatsApp',
            $parts[0],
            $parts[1],
            $parts[2]
        );
    }

    private function eksekusiCancelOrderKeController($waNumber, $user, $session, $orderNumber, $reason, $bank, $norek, $nama)
    {
        $req = Request::create('/api/pembeli/checkout/cancel', 'POST', [
            'order_number'        => $orderNumber,
            'cancellation_reason' => $reason,
            'refund_bank'         => $bank,
            'refund_norek'        => $norek,
            'refund_nama'         => $nama,
        ]);
        $req->setUserResolver(fn() => $user);

        $res = app(CheckoutController::class)->cancelOrder($req);
        $resData = $res->getData(true);

        $sd = is_array($session->session_data) ? $session->session_data : [];
        unset($sd['cancel_order_number'], $sd['cancel_is_paid'], $sd['cancel_reason']);
        $session->update(['session_data' => $sd]);

        if (($resData['status'] ?? 'error') === 'success') {
            $infoRefund = $bank
                ? "Dana refund akan segera diproses oleh Admin ke rekening *{$bank} - {$norek} a/n {$nama}* maksimal 1x24 jam."
                : "Pesanan telah dibatalkan.";

            $reply = $this->getTemplate('batal_pesanan_berhasil', [
                'order_number' => $orderNumber,
                'info_refund'  => $infoRefund
            ]) ?? "✅ Pesanan *#{$orderNumber}* berhasil dibatalkan.\n{$infoRefund}";

            $this->sendMessage($waNumber, $reply);
        } else {
            $this->sendMessage($waNumber, "⚠️ " . ($resData['message'] ?? 'Gagal membatalkan pesanan.'));
        }

        $this->stepRiwayatPesanan($waNumber, $user, $session, $sd['riwayat_filter'] ?? 'semua', $sd['riwayat_page'] ?? 1);
    }

    // ==========================================
    // ENDPOINT KOORDINAT MAPS & HELPER FONNTE
    // ==========================================

    public function updateKoordinatMap(Request $request)
    {
        $addr = UserAddress::find($request->id);

        if (!$addr || $addr->recipient_whatsapp !== $request->wa) {
            return response()->json(['status' => 'error', 'message' => 'Otorisasi gagal.'], 403);
        }

        $roId = $this->getRajaOngkirId($addr->district, $addr->city);

        $dapur = CompanyProfile::first();
        $jarak = 0;
        if ($dapur && $dapur->latitude && $dapur->longitude) {
            $jarak = $this->hitungJarak($dapur->latitude, $dapur->longitude, $request->latitude, $request->longitude);
        }

        $addr->update([
            'latitude'                  => $request->latitude,
            'longitude'                 => $request->longitude,
            'rajaongkir_destination_id' => $roId,
            'distance_to_store'         => $jarak
        ]);

        $session = WhatsappSession::where('whatsapp_number', $request->wa)->first();
        if ($session && $session->current_step === 'checkout_alamat') {
            $sessionData = $session->session_data;

            $sessionData['co_address_id'] = $addr->id;
            $patokan = $addr->landmark ? " (Patokan: {$addr->landmark})" : "";
            $sessionData['co_alamat'] = "{$addr->street_address}, {$addr->district}, {$addr->city}, {$addr->province}{$patokan}";
            $sessionData['co_ro_id'] = $roId;
            $sessionData['co_distance'] = $jarak;

            $session->update(['session_data' => $sessionData]);

            $user = User::find($addr->user_id);
            if ($user) {
                Auth::setUser($user);
                $this->stepCekOngkirDanJadwal($request->wa, $user, $session);
            }
        }

        return response()->json(['status' => 'success']);
    }

    private function getRajaOngkirId($district, $city)
    {
        try {
            $response = Http::withHeaders([
                'key' => env('RAJAONGKIR_API_KEY')
            ])->get('https://rajaongkir.komerce.id/api/v1/destination/domestic-destination', [
                'search' => $district
            ]);

            if ($response->successful() && isset($response->json()['data'])) {
                $cityClean = strtolower(str_replace(['kota ', 'kabupaten ', 'kab. '], '', $city));
                foreach ($response->json()['data'] as $dest) {
                    if (str_contains(strtolower($dest['city_name']), $cityClean)) {
                        return $dest['id'];
                    }
                }
                return $response->json()['data'][0]['id'] ?? null;
            }
        } catch (\Exception $e) {}
        return null;
    }

    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * asin(sqrt($a));
        return round($earthRadius * $c, 2);
    }

    private function sendMessage($target, $message)
    {
        try {
            Http::asForm()->withHeaders([
                'Authorization' => env('FONNTE_TOKEN'),
            ])->post('https://api.fonnte.com/send', [
                'target'  => $target,
                'message' => $message,
                'typing'  => true,
                'delay'   => '1',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal kirim WA Bot: ' . $e->getMessage());
        }
    }
}
