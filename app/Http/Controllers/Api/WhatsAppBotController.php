<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\WhatsappSession;
use App\Models\BotTemplate;
use App\Models\Product;
use App\Models\ProductVariant;

class WhatsAppBotController extends Controller
{
    public function webhook(Request $request)
    {
        $sender = $request->input('sender');
        $message = trim($request->input('message'));

        if (empty($sender) || $message === '' || $message === null) {
            return response()->json(['status' => 'ignored']);
        }

        $waNumber = preg_replace('/[^0-9]/', '', $sender);
        if (str_starts_with($waNumber, '0')) {
            $waNumber = '62' . substr($waNumber, 1);
        }

        $user = User::firstOrCreate(
            ['whatsapp_number' => $waNumber],
            [
                'name' => 'Pelanggan ' . substr($waNumber, -4),
                'password' => Hash::make(Str::random(10)),
                'role' => 'pelanggan'
            ]
        );

        $session = WhatsappSession::firstOrCreate(
            ['whatsapp_number' => $waNumber],
            ['current_step' => 'sapaan', 'session_data' => []]
        );

        $session->update(['last_interaction_at' => now()]);

        $sessionData = is_array($session->session_data) ? $session->session_data : [];

        // ==========================================
        // STATE MACHINE (ROUTING CHAT)
        // ==========================================
        switch ($session->current_step) {
            case 'sapaan':
                if ($message == '1') {
                    $this->stepLihatKategori($waNumber, $session);
                } else {
                    $this->stepSapaan($waNumber, $user, $session);
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
                    $this->sendMessage($waNumber, "Fitur masuk keranjang segera hadir! Kita balik ke menu utama dulu ya.");
                    $this->stepSapaan($waNumber, $user, $session);
                } else {
                    $this->sendMessage($waNumber, "Pilihan tidak valid. Balas 1 untuk tambah ke keranjang, atau 0 untuk kembali.");
                }
                break;

            default:
                $this->stepSapaan($waNumber, $user, $session);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    // ==========================================
    // FUNGSI-FUNGSI LOGIC PER STEP (FULL DINAMIS)
    // ==========================================

    private function stepSapaan($waNumber, $user, $session)
    {
        $session->update(['current_step' => 'sapaan']);
        $template = BotTemplate::where('session_state', 'sapaan')->where('is_active', true)->first();

        if ($template) {
            $reply = str_replace('{nama}', $user->name, $template->message_text);
        } else {
            $reply = "Halo Kak {$user->name}! Selamat datang di Raya Kitchen. 🍞✨\n\nKetik angka dari menu di bawah ini untuk mulai:\n1. 📖 Lihat Katalog Produk\n2. 🛒 Cek Keranjang Saya\n3. 📦 Cek Status Pesanan\n4. ℹ️ Cara Pemesanan";
        }
        $this->sendMessage($waNumber, $reply);
    }

    private function stepLihatKategori($waNumber, $session)
    {
        $session->update(['current_step' => 'lihat_kategori']);

        $categories = Product::where('is_active', true)->get();
        if ($categories->isEmpty()) {
            $this->sendMessage($waNumber, "Maaf, belum ada kategori produk yang aktif.\n\n0. 🔙 Kembali ke Menu Utama");
            return;
        }

        $daftarKategori = "";
        foreach ($categories as $index => $cat) {
            $nomor = $index + 1;
            $daftarKategori .= "{$nomor}. {$cat->name}\n";
        }

        // Ambil template dari database
        $template = BotTemplate::where('session_state', 'lihat_kategori')->where('is_active', true)->first();
        if ($template) {
            $reply = str_replace('{daftar_kategori}', $daftarKategori, $template->message_text);
        } else {
            // Fallback kalau belum diset di panel admin
            $reply = "Silakan pilih kategori produk yang mau dilihat:\n\n{$daftarKategori}\n0. 🔙 Kembali ke Menu Utama";
        }

        $this->sendMessage($waNumber, $reply);
    }

    private function prosesPilihKategori($waNumber, $message, $session)
    {
        $categories = Product::where('is_active', true)->get();
        $index = ((int) $message) - 1;

        if (isset($categories[$index])) {
            $selectedProduct = $categories[$index];
            $this->stepLihatVarian($waNumber, $session, $selectedProduct->id);
        } else {
            $this->sendMessage($waNumber, "Nomor kategori tidak valid. Silakan balas dengan angka yang ada di daftar, atau 0 untuk kembali.");
        }
    }

    private function stepLihatVarian($waNumber, $session, $productId)
    {
        $sessionData = is_array($session->session_data) ? $session->session_data : [];
        $sessionData['selected_product_id'] = $productId;

        $session->update([
            'current_step' => 'lihat_varian',
            'session_data' => $sessionData
        ]);

        $variants = ProductVariant::where('product_id', $productId)->get();
        $product = Product::find($productId);

        if ($variants->isEmpty()) {
            $this->sendMessage($waNumber, "Maaf, belum ada varian untuk kategori {$product->name}.\n\n0. 🔙 Kembali ke Kategori");
            return;
        }

        $daftarVarian = "";
        foreach ($variants as $index => $var) {
            $nomor = $index + 1;
            $harga = number_format($var->price, 0, ',', '.');
            $daftarVarian .= "{$nomor}. {$var->variant_name} (Rp {$harga})\n";
        }

        // Ambil template dari database
        $template = BotTemplate::where('session_state', 'lihat_varian')->where('is_active', true)->first();
        if ($template) {
            $reply = $template->message_text;
            $reply = str_replace('{nama_kategori}', $product->name, $reply);
            $reply = str_replace('{daftar_varian}', $daftarVarian, $reply);
        } else {
            // Fallback
            $reply = "Kategori: *{$product->name}*\nPilih varian yang mau dilihat detailnya:\n\n{$daftarVarian}\n0. 🔙 Kembali ke Daftar Kategori";
        }

        $this->sendMessage($waNumber, $reply);
    }

    private function prosesPilihVarian($waNumber, $message, $session)
    {
        $sessionData = is_array($session->session_data) ? $session->session_data : [];
        $productId = $sessionData['selected_product_id'] ?? null;

        if (!$productId) {
            $this->stepLihatKategori($waNumber, $session);
            return;
        }

        $variants = ProductVariant::where('product_id', $productId)->get();
        $index = ((int) $message) - 1;

        if (isset($variants[$index])) {
            $selectedVariant = $variants[$index];
            $this->stepDetailVarian($waNumber, $session, $selectedVariant);
        } else {
            $this->sendMessage($waNumber, "Nomor varian tidak valid. Silakan balas dengan angka yang ada, atau 0 untuk kembali.");
        }
    }

    private function stepDetailVarian($waNumber, $session, $variant)
    {
        $sessionData = is_array($session->session_data) ? $session->session_data : [];
        $sessionData['selected_variant_id'] = $variant->id;

        $session->update([
            'current_step' => 'detail_varian',
            'session_data' => $sessionData
        ]);

        $harga = number_format($variant->price, 0, ',', '.');
        $kategori = $variant->product->name ?? '-';
        $deskripsi = $variant->product->description ?? 'Tidak ada deskripsi.';

        // Ambil template dari database
        $template = BotTemplate::where('session_state', 'detail_varian')->where('is_active', true)->first();
        if ($template) {
            $reply = $template->message_text;
            $reply = str_replace('{nama_produk}', $variant->variant_name, $reply);
            $reply = str_replace('{kategori_produk}', $kategori, $reply);
            $reply = str_replace('{harga_produk}', $harga, $reply);
            $reply = str_replace('{deskripsi_produk}', $deskripsi, $reply);
        } else {
            // Fallback
            $reply = "Detail Produk:\n\n🍞 *{$variant->variant_name}*\n🏷️ Kategori: {$kategori}\n💰 Harga: Rp {$harga}\n\n📝 Deskripsi:\n{$deskripsi}\n\nKetik angka berikut:\n1. 🛒 Tambah ke Keranjang\n0. 🔙 Kembali ke Daftar Varian";
        }

        // AMBIL URL GAMBAR UNTUK FONNTE
        $imageUrl = null;
        if (!empty($variant->image_url)) {
            $namaGambar = ltrim($variant->image_url, '/');
            // Pastikan URL mengarah ke domain ngrok/server saat ini
            $host = rtrim(request()->getSchemeAndHttpHost(), '/');
            $imageUrl = $host . "/storage/produk/{$namaGambar}";
        }


        $imageUrl = "https://picsum.photos/400/400.jpg";

        $this->sendMessage($waNumber, $reply, $imageUrl);
    }

    // ==========================================
    // FUNGSI HELPER FONNTE
    // ==========================================

    private function sendMessage($target, $message, $imageUrl = null)
    {
        try {
            $payload = [
                'target' => $target,
                'message' => $message,
                'typing' => true,
                'delay' => '1',
            ];

            // Masukin link gambar kalau ada
            if ($imageUrl) {
                $payload['url'] = $imageUrl;
                Log::info("Kirim URL Gambar ke Fonnte: " . $imageUrl);
            }

            Http::asForm()->withHeaders([
                'Authorization' => env('FONNTE_TOKEN'),
            ])->post('https://api.fonnte.com/send', $payload);

        } catch (\Exception $e) {
            Log::error('Gagal kirim WA Bot: ' . $e->getMessage());
        }
    }
}
