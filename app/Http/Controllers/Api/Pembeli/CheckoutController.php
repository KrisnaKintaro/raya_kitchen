<?php

namespace App\Http\Controllers\Api\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CompanyProfile;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input dari frontend
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'delivery_date' => 'required|date',
            'delivery_time' => 'required',
            'payment_method' => 'required|in:transfer_full,transfer_dp,cod'
        ]);

        $userId = Auth::id() ?? 1;
        $cartItems = Cart::with('productVariant')->where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Keranjang belanja masih kosong!'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item->productVariant->price * $item->quantity;
            }

            // Hitung DP 30% murni dari harga barang
            $dpAmount = $totalAmount * 0.3;

            $pilihanBayar = $request->input('payment_method');

            // LOGIC SISA TAGIHAN: Kalo lunas sisa 0, kalo DP/COD sisa 70%
            $remainingAmount = ($pilihanBayar === 'transfer_full') ? 0 : ($totalAmount - $dpAmount);

            $orderNumber = 'RK-' . date('Ymd') . '-' . rand(1000, 9999);

            // Simpan ke tabel orders
            $order = Order::create([
                'user_id' => $userId,
                'order_number' => $orderNumber,
                'recipient_name' => $request->input('name'),
                'recipient_whatsapp' => $request->input('phone'),
                'shipping_address' => $request->input('address'),
                'delivery_date' => $request->input('delivery_date'),
                'delivery_time' => $request->input('delivery_time'),
                'total_amount' => $totalAmount,
                'dp_amount' => $dpAmount,
                'remaining_amount' => $remainingAmount, // Simpan sisa tagihannya di sini
                'status' => 'pending',
                'order_source' => 'web',
            ]);

            // Simpan ke tabel order_items
            foreach ($cartItems as $item) {
                $price = $item->productVariant->price;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'price_at_order' => $price,
                    'subtotal' => $price * $item->quantity,
                ]);
            }

            $pilihanBayar = $request->input('payment_method');
            $amountToPay = ($pilihanBayar === 'transfer_full') ? $totalAmount : $dpAmount;

            $tipeTransaksi = ($pilihanBayar === 'cod') ? 'cod' : 'transfer';

            // Insert Transaction
            $transaction = Transaction::create([
                'order_id' => $order->id,
                'amount' => $amountToPay,
                'payment_method' => $tipeTransaksi,
                'payment_status' => 'unpaid'
            ]);

            // Kosongkan keranjang setelah order berhasil ke-create
            Cart::where('user_id', $userId)->delete();

            DB::commit();

            // =========================================================
            // INTEGRASI DUITKU SANDBOX (VIA PACKAGE RESMI)
            // =========================================================
            if ($tipeTransaksi !== 'cod') {
                $merchantCode = env('DUITKU_MERCHANT_CODE');
                $merchantKey = env('DUITKU_MERCHANT_KEY');

                // Konfigurasi Duitku
                $duitkuConfig = new \Duitku\Config($merchantKey, $merchantCode);
                $duitkuConfig->setSandboxMode(true);
                $duitkuConfig->setSanitizedMode(true);
                $duitkuConfig->setDuitkuLogs(false);

                // Kode pembayaran untuk QRIS (ShopeePay/QRIS Universal di Duitku biasanya 'SP' atau 'NQ')
                // Kalau lu mau QRIS murni, kode metode pembayarannya "SP"
                $paymentMethod = "SP";
                $paymentAmount = (int) $amountToPay;

                $params = [
                    'paymentAmount'     => $paymentAmount,
                    'paymentMethod'     => $paymentMethod,
                    'merchantOrderId'   => $orderNumber,
                    'productDetails'    => 'Pembayaran Pesanan ' . $orderNumber,
                    'email'             => 'customer@rayakitchen.com',
                    'phoneNumber'       => $request->input('phone'),
                    'customerVaName'    => $request->input('name'),
                    'callbackUrl'       => url('/api/duitku/callback'),
                    'returnUrl'         => url('/riwayat-pesanan?success=true'),
                    'expiryPeriod'      => 60 // 1 jam
                ];

                try {
                    // Pake Duitku\Api buat bypass halaman Pop Duitku dan langsung dapet QRIS-nya
                    $responseDuitkuApi = \Duitku\Api::createInvoice($params, $duitkuConfig);
                    $result = json_decode($responseDuitkuApi, true);

                    if ($result['statusCode'] == '00') {
                        // Simpan qr_string dari Duitku ke database
                        $transaction->update([
                            'reference' => $result['reference'] ?? null,
                            'qr_string' => $result['qrString'] ?? null
                        ]);

                        return response()->json([
                            'status' => 'success',
                            'message' => 'Pesanan dibuat! Lanjut scan QRIS...',
                            'data' => [
                                'order_number' => $orderNumber,
                                // Paksa sistem tetap di halaman web lu (jangan pake paymentUrl Duitku)
                                'redirect_url' => url('/pembayaran/' . $orderNumber)
                            ]
                        ], 200);
                    } else {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Gagal terhubung ke Duitku: ' . ($result['statusMessage'] ?? 'Unknown Error')
                        ], 500);
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Exception Duitku: ' . $e->getMessage()
                    ], 500);
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $userId = Auth::id() ?? 1;
        $orders = Order::with('transaction')->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Lazy Evaluation: Cek dan batalkan yang expired sebelum dikirim ke frontend
        foreach ($orders as $order) {
            $expiredAt = $order->created_at->addMinutes(60);
            if ($order->status === 'pending' && $order->transaction->payment_status === 'unpaid' && now()->greaterThanOrEqualTo($expiredAt)) {
                $order->update(['status' => 'dibatalkan', 'cancellation_reason' => 'Otomatis dibatalkan: Waktu pembayaran QRIS habis (60 menit).']);
                $order->transaction->update(['payment_status' => 'failed']);
            }
        }

        $mappedOrders = $orders->map(function ($order) {
            $isDP = $order->transaction->amount < $order->total_amount;
            return [
                'order_number' => $order->order_number,
                'status' => strtoupper(str_replace('_', ' ', $order->status)),
                'payment_method' => $order->transaction->payment_method ?? '-',
                'payment_status' => $order->transaction->payment_status ?? 'unpaid',
                'tipe_bayar' => $isDP ? 'DP 30%' : 'Lunas',
                'date' => $order->created_at->format('d M Y, H:i'),
                'recipient_name' => $order->recipient_name,
                'recipient_whatsapp' => $order->recipient_whatsapp,
                'shipping_address' => $order->shipping_address,
                'total_amount_formatted' => 'Rp ' . number_format($order->total_amount, 0, ',', '.'),
                'tagihan_sekarang' => 'Rp ' . number_format($order->transaction->amount, 0, ',', '.'),
            ];
        });

        return response()->json(['status' => 'success', 'data' => $mappedOrders]);
    }

    public function showNota(Request $request)
    {
        $orderNumber = $request->query('order_number');

        $order = Order::with(['items.productVariant.product', 'transaction'])
            ->where('order_number', $orderNumber)
            ->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Nota tidak ditemukan.'], 404);
        }

        $company = CompanyProfile::first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'order' => $order,
                'company' => $company
            ]
        ]);
    }

    public function storeAddressAjax(Request $request)
    {
        $validated = $request->validate([
            'label'          => 'required|string|max:50',
            'province'       => 'required|string',
            'city'           => 'required|string',
            'district'       => 'required|string',
            'postal_code'    => 'required|string|max:10',
            'street_address' => 'required|string',
        ]);

        $user = $request->user();
        $isPrimary = $user->addresses()->doesntExist();

        $address = $user->addresses()->create(array_merge($validated, [
            'recipient_name'     => $user->name,
            'recipient_whatsapp' => $user->whatsapp_number,
            'is_primary'         => $isPrimary,
        ]));

        $prefix = $isPrimary ? '[Utama] ' : '';
        $teksLengkap = "{$prefix}{$address->label} - {$address->street_address}, {$address->district}, {$address->city}, {$address->province} {$address->postal_code}";

        return response()->json([
            'status' => 'success',
            'data'   => [
                'value' => $teksLengkap,
                'text'  => $teksLengkap
            ]
        ]);
    }

    // Nampilin Halaman Staging QRIS (Dirobah jadi full JSON)
    public function showPembayaran($orderNumber)
    {
        $order = Order::with('transaction')->where('order_number', $orderNumber)->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        $expiredAt = $order->created_at->addMinutes(60);

        if ($order->status === 'pending' && $order->transaction->payment_status === 'unpaid' && now()->greaterThanOrEqualTo($expiredAt)) {
            $order->update(['status' => 'dibatalkan', 'cancellation_reason' => 'Otomatis dibatalkan: Waktu pembayaran QRIS habis.']);
            $order->transaction->update(['payment_status' => 'failed']);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'order' => $order,
                'expired_at' => $expiredAt->toIso8601String()
            ]
        ]);
    }

    // Endpoint buat Polling AJAX tiap 3 detik
    public function cekStatusPembayaran($orderNumber)
    {
        $order = Order::with('transaction')->where('order_number', $orderNumber)->first();
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order tidak ditemukan'], 404);
        }

        // Cek kedaluwarsa secara real-time saat polling
        $expiredAt = $order->created_at->addMinutes(60);
        if ($order->status === 'pending' && $order->transaction->payment_status === 'unpaid' && now()->greaterThanOrEqualTo($expiredAt)) {
            $order->update(['status' => 'dibatalkan', 'cancellation_reason' => 'Otomatis dibatalkan: Waktu pembayaran QRIS habis.']);
            $order->transaction->update(['payment_status' => 'failed']);
            return response()->json(['status' => 'success', 'payment_status' => 'failed']);
        }

        return response()->json(['status' => 'success', 'payment_status' => $order->transaction->payment_status]);
    }

    public function cancelOrder(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
            'cancellation_reason' => 'required|string',
            // refund_account_number opsional, cuma diisi kalo user udah terlanjur bayar DP/Full
        ]);

        $userId = Auth::id() ?? 1;

        // Cari pesanan milik user yang lagi login
        $order = Order::with('transaction')
            ->where('order_number', $request->order_number)
            ->where('user_id', $userId)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan.'
            ], 404);
        }

        // ATURAN PENTING: Cuma bisa dibatalin kalo statusnya masih pending
        if ($order->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan sudah diproses dan tidak bisa dibatalkan.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // 1. Update tabel orders
            $order->update([
                'status' => 'dibatalkan',
                'cancellation_reason' => $request->cancellation_reason,
                'refund_account_number' => $request->refund_account_number,
            ]);

            // 2. Update tabel transactions jadi failed
            if ($order->transaction) {
                $order->transaction->update([
                    'payment_status' => 'failed'
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil dibatalkan.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membatalkan pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function duitkuCallback(Request $request)
    {
        $merchantCode = env('DUITKU_MERCHANT_CODE');
        $merchantKey = env('DUITKU_MERCHANT_KEY');

        $duitkuConfig = new \Duitku\Config($merchantKey, $merchantCode);
        $duitkuConfig->setSandboxMode(true);

        try {
            $callback = \Duitku\Api::callback($duitkuConfig);
            $notif = json_decode($callback);

            if ($notif->resultCode == "00") {
                // Hapus tulisan "-LUNAS" kalau ada, biar dapet order_number aslinya
                $cleanOrderId = str_replace('-LUNAS', '', $notif->merchantOrderId);

                // Pembayaran Sukses
                $order = Order::with('transaction')->where('order_number', $cleanOrderId)->first();
                if ($order) {
                    // Cek apakah bayar DP atau Lunas (baik lunas langsung atau lunas belakangan)
                    if ($notif->amount >= $order->remaining_amount || $notif->amount == $order->total_amount) {
                        $order->update(['remaining_amount' => 0]);
                        $order->transaction->update(['payment_status' => 'paid_full']);
                    } else {
                        $order->transaction->update(['payment_status' => 'paid_dp']);
                    }
                }
            } else if ($notif->resultCode == "01") {
                $cleanOrderId = str_replace('-LUNAS', '', $notif->merchantOrderId);

                // Pembayaran Gagal
                $order = Order::with('transaction')->where('order_number', $cleanOrderId)->first();
                if ($order) {
                    $order->transaction->update(['payment_status' => 'failed']);
                }
            }

            return response()->json(['message' => 'Callback Processed']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function payRemaining(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string'
        ]);

        $userId = Auth::id() ?? 1;

        $order = Order::with('transaction')
            ->where('order_number', $request->order_number)
            ->where('user_id', $userId)
            ->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($order->remaining_amount <= 0 || $order->transaction->payment_status === 'paid_full') {
            return response()->json(['status' => 'error', 'message' => 'Pesanan ini sudah lunas cuy.'], 400);
        }

        // =========================================================
        // BIKIN INVOICE DUITKU BARU KHUSUS UNTUK PELUNASAN
        // =========================================================
        $merchantCode = env('DUITKU_MERCHANT_CODE');
        $merchantKey = env('DUITKU_MERCHANT_KEY');

        $duitkuConfig = new \Duitku\Config($merchantKey, $merchantCode);
        $duitkuConfig->setSandboxMode(true);
        $duitkuConfig->setSanitizedMode(true);
        $duitkuConfig->setDuitkuLogs(false);

        // Kasih suffix khusus biar order ID di Duitku ga bentrok sama ID pas DP
        $pelunasanOrderId = $order->order_number . '-LUNAS';

        $params = [
            'paymentAmount'     => (int) $order->remaining_amount,
            'paymentMethod'     => "SP", // QRIS
            'merchantOrderId'   => $pelunasanOrderId,
            'productDetails'    => 'Pelunasan Pesanan ' . $order->order_number,
            'email'             => 'customer@rayakitchen.com',
            'phoneNumber'       => $order->recipient_whatsapp,
            'customerVaName'    => $order->recipient_name,
            'callbackUrl'       => url('/api/duitku/callback'),
            'returnUrl'         => url('/riwayat-pesanan?success=true'),
            'expiryPeriod'      => 60 // 1 jam
        ];

        try {
            $responseDuitkuApi = \Duitku\Api::createInvoice($params, $duitkuConfig);
            $result = json_decode($responseDuitkuApi, true);

            if ($result['statusCode'] == '00') {
                // Update amount transaksi jadi total full, ganti qr_string pake yang baru
                $order->transaction->update([
                    'amount' => $order->total_amount, // Kembalikan ke nominal full
                    'reference' => $result['reference'] ?? null,
                    'qr_string' => $result['qrString'] ?? null
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'QRIS Pelunasan berhasil dibuat!',
                    'data' => [
                        'redirect_url' => url('/pembayaran/' . $order->order_number)
                    ]
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal terhubung ke Duitku: ' . ($result['statusMessage'] ?? 'Unknown Error')
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exception Duitku: ' . $e->getMessage()
            ], 500);
        }
    }
}
