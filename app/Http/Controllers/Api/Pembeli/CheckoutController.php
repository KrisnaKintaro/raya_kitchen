<?php

namespace App\Http\Controllers\Api\Pembeli;

use App\Http\Controllers\Api\Pembeli\NotificationController;
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
            'delivery_date' => 'required',
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

            // Ambil ongkos kirim dari request form
            $shippingCost = (float) $request->input('shipping_cost', 0);
            $grandTotal = $totalAmount + $shippingCost;

            // Hitung DP 30% dari Grand Total
            $dpAmount = $grandTotal * 0.3;

            $pilihanBayar = $request->input('payment_method');
            $remainingAmount = ($pilihanBayar === 'transfer_full') ? 0 : ($grandTotal - $dpAmount);

            $orderNumber = 'RK-' . date('Ymd') . '-' . rand(1000, 9999);

            // =========================================================================
            // LOGIKA PENENTUAN JADWAL PENGIRIMAN (ANTI-TABRAKAN DAPUR) - [DI SINI LETAKNYA]
            // =========================================================================
            $isInternal = ($shippingCost == 0);

            if ($request->input('delivery_date') === 'auto' || (!$isInternal && $shippingCost > 0)) {
                // JIKA EKSPEDISI: Cari tanggal kosong secara otomatis mulai H+1
                $tanggalOtomatis = now()->addDay();
                
                while (true) {
                    $tglStr = $tanggalOtomatis->format('Y-m-d');
                    
                    // Hitung total roti yang sudah dipesan di tanggal tersebut
                    $totalRotiDipesan = OrderItem::whereHas('order', function($q) use ($tglStr) {
                        $q->where('delivery_date', $tglStr)->where('status', '!=', 'dibatalkan');
                    })->sum('quantity');

                    $batasMaksimalDapur = 50; // Kapasitas maksimal harian dapur

                    $totalRotiCheckout = 0;
                    foreach($cartItems as $item) {
                        $totalRotiCheckout += $item->quantity;
                    }

                    // Jika masih muat, tetapkan tanggal ini
                    if (($totalRotiDipesan + $totalRotiCheckout) <= $batasMaksimalDapur) {
                        $deliveryDate = $tglStr;
                        $deliveryTime = '10:00:00'; // Jam standar ekspedisi
                        break;
                    }

                    $tanggalOtomatis->addDay();
                }
            } else {
                // JIKA KURIR INTERNAL: Ambil tanggal & jam pilihan pembeli dari kalender
                $deliveryDate = $request->input('delivery_date');
                $deliveryTime = $request->input('delivery_time');
            }

            // Simpan ke tabel orders
            $order = Order::create([
                'user_id' => $userId,
                'order_number' => $orderNumber,
                'recipient_name' => $request->input('name'),
                'recipient_whatsapp' => $request->input('phone'),
                'shipping_address' => $request->input('address'),
                'delivery_date' => $deliveryDate,
                'delivery_time' => $deliveryTime,
                'shipping_cost' => $shippingCost,
                'courier_name' => $request->input('courier_name'),
                'shipping_service' => $request->input('shipping_service'),
                'total_amount' => $grandTotal,
                'dp_amount' => $dpAmount,
                'remaining_amount' => $remainingAmount,
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

            $amountToPay = ($pilihanBayar === 'transfer_full') ? $grandTotal : $dpAmount;
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

                $duitkuConfig = new \Duitku\Config($merchantKey, $merchantCode);
                $duitkuConfig->setSandboxMode(true);
                $duitkuConfig->setSanitizedMode(true);
                $duitkuConfig->setDuitkuLogs(false);

                $paymentMethod = "SP"; // QRIS
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
                    $responseDuitkuApi = \Duitku\Api::createInvoice($params, $duitkuConfig);
                    $result = json_decode($responseDuitkuApi, true);

                    if ($result['statusCode'] == '00') {
                        $transaction->update([
                            'reference' => $result['reference'] ?? null,
                            'qr_string' => $result['qrString'] ?? null
                        ]);

                        return response()->json([
                            'status' => 'success',
                            'message' => 'Pesanan dibuat! Lanjut scan QRIS...',
                            'data' => [
                                'order_number' => $orderNumber,
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

        foreach ($orders as $order) {
            $expiredAt = $order->created_at->addMinutes(60);
            if ($order->status === 'pending' && $order->transaction->payment_status === 'unpaid' && now()->greaterThanOrEqualTo($expiredAt)) {
                $order->update(['status' => 'dibatalkan', 'cancellation_reason' => 'Otomatis dibatalkan: Waktu pembayaran QRIS habis (60 menit).']);
                $order->transaction->update(['payment_status' => 'failed']);
                NotificationController::send($order, 'batal_otomatis');
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
            NotificationController::send($order, 'batal_otomatis');
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'order' => $order,
                'expired_at' => $expiredAt->toIso8601String()
            ]
        ]);
    }

    public function cekStatusPembayaran($orderNumber)
    {
        $order = Order::with('transaction')->where('order_number', $orderNumber)->first();
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order tidak ditemukan'], 404);
        }

        $expiredAt = $order->created_at->addMinutes(60);
        if ($order->status === 'pending' && $order->transaction->payment_status === 'unpaid' && now()->greaterThanOrEqualTo($expiredAt)) {
            $order->update(['status' => 'dibatalkan', 'cancellation_reason' => 'Otomatis dibatalkan: Waktu pembayaran QRIS habis.']);
            $order->transaction->update(['payment_status' => 'failed']);
            NotificationController::send($order, 'batal_otomatis');
            return response()->json(['status' => 'success', 'payment_status' => 'failed']);
        }

        return response()->json(['status' => 'success', 'payment_status' => $order->transaction->payment_status]);
    }

    public function cancelOrder(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
            'cancellation_reason' => 'required|string',
            'refund_bank' => 'nullable|string',
            'refund_norek' => 'nullable|string',
            'refund_nama' => 'nullable|string',
        ]);

        $userId = Auth::id() ?? 1;

        $order = Order::with('transaction')
            ->where('order_number', $request->order_number)
            ->where('user_id', $userId)
            ->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json(['status' => 'error', 'message' => 'Pesanan sudah diproses dan tidak bisa dibatalkan.'], 400);
        }

        $refundAccountString = null;
        if ($request->refund_bank && $request->refund_norek && $request->refund_nama) {
            $refundAccountString = $request->refund_bank . ' - ' . $request->refund_norek . ' - ' . $request->refund_nama;
        }

        DB::beginTransaction();
        try {
            $order->update([
                'status' => 'dibatalkan',
                'cancellation_reason' => $request->cancellation_reason,
                'refund_account_number' => $refundAccountString,
                'refund_status' => $refundAccountString ? 'pending' : 'none'
            ]);

            if ($order->transaction) {
                $order->transaction->update([
                    'payment_status' => 'failed'
                ]);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Pesanan berhasil dibatalkan.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal membatalkan pesanan: ' . $e->getMessage()], 500);
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
                $cleanOrderId = str_replace('-LUNAS', '', $notif->merchantOrderId);

                $order = Order::with('transaction')->where('order_number', $cleanOrderId)->first();
                if ($order) {
                    if ($notif->amount >= $order->remaining_amount || $notif->amount == $order->total_amount) {
                        $order->update(['remaining_amount' => 0]);
                        $order->transaction->update(['payment_status' => 'paid_full']);
                    } else {
                        $order->transaction->update(['payment_status' => 'paid_dp']);
                    }
                }
            } else if ($notif->resultCode == "01") {
                $cleanOrderId = str_replace('-LUNAS', '', $notif->merchantOrderId);

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

    public function cekOngkir(Request $request)
    {
        $alamat = \App\Models\UserAddress::find($request->address_id);
        $setting = \App\Models\StoreSetting::first();
        
        $max_jarak = $setting->free_delivery_max_distance ?? 10;

        if (!$alamat || $alamat->distance_to_store === null) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Titik peta alamat belum diset. Silakan edit alamat Anda dan tandai di peta.'
            ], 400);
        }

        // 1. JIKA JARAK DEKAT (Kurir Internal Toko / Gratis Ongkir)
        if ($alamat->distance_to_store <= $max_jarak) {
            return response()->json([
                'status' => 'success',
                'is_internal' => true,
                'courier_name' => 'Kurir Internal Raya Kitchen',
                'shipping_service' => 'Gratis Ongkir (' . $alamat->distance_to_store . ' KM)',
                'shipping_cost' => 0
            ]);
        }

        // 2. JIKA JARAK JAUH / LUAR KOTA / LUAR PROVINSI (Menggunakan Rumus Umum Logistik)
        $userId = $request->user()->id ?? Auth::id();
        $cartItems = Cart::with('productVariant')->where('user_id', $userId)->get();
        
        $totalBeratGram = 0;
        foreach($cartItems as $item) {
            $beratSatuan = $item->productVariant->weight ?? 500; 
            $totalBeratGram += ($beratSatuan * $item->quantity);
        }

        if ($totalBeratGram < 100) {
            $totalBeratGram = 1000; 
        }

        $beratKg = $totalBeratGram / 1000;
        $jarakKm = $alamat->distance_to_store;

        $tarifDasar = 10000;              
        $biayaPerKm = 150;                
        $biayaPerKg = 4000;               

        $estimasiOngkir = $tarifDasar + ($jarakKm * $biayaPerKm) + ($beratKg * $biayaPerKg);

        if ($jarakKm > 100 && $estimasiOngkir < 18000) {
            $estimasiOngkir = 18000;
        }

        $shippingCost = round($estimasiOngkir, -3); 

        return response()->json([
            'status' => 'success',
            'is_internal' => false,
            'courier_name' => 'JNE REG / SiCepat',
            'shipping_service' => 'Estimasi 2-3 Hari (' . $beratKg . ' kg / ' . $jarakKm . ' KM)',
            'shipping_cost' => $shippingCost
        ]);
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

        $merchantCode = env('DUITKU_MERCHANT_CODE');
        $merchantKey = env('DUITKU_MERCHANT_KEY');

        $duitkuConfig = new \Duitku\Config($merchantKey, $merchantCode);
        $duitkuConfig->setSandboxMode(true);
        $duitkuConfig->setSanitizedMode(true);
        $duitkuConfig->setDuitkuLogs(false);

        $pelunasanOrderId = $order->order_number . '-LUNAS';

        $params = [
            'paymentAmount'     => (int) $order->remaining_amount,
            'paymentMethod'     => "SP", 
            'merchantOrderId'   => $pelunasanOrderId,
            'productDetails'    => 'Pelunasan Pesanan ' . $order->order_number,
            'email'             => 'customer@rayakitchen.com',
            'phoneNumber'       => $order->recipient_whatsapp,
            'customerVaName'    => $order->recipient_name,
            'callbackUrl'       => url('/api/duitku/callback'),
            'returnUrl'         => url('/riwayat-pesanan?success=true'),
            'expiryPeriod'      => 60 
        ];

        try {
            $responseDuitkuApi = \Duitku\Api::createInvoice($params, $duitkuConfig);
            $result = json_decode($responseDuitkuApi, true);

            if ($result['statusCode'] == '00') {
                $order->transaction->update([
                    'amount' => $order->total_amount, 
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

    public function getHeatmap(Request $request)
    {
        $bulan = $request->query('bulan', now()->month);
        $tahun = $request->query('tahun', now()->year);

        $heatmap = [];
        $daysInMonth = \Carbon\Carbon::create($tahun, $bulan)->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = sprintf('%04d-%02d-%02d', $tahun, $bulan, $day);
            $heatmap[$dateStr] = 'low'; 
        }

        return response()->json([
            'status' => 'success',
            'data' => $heatmap
        ]);
    }

    public function getAvailableTimes(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                '09:00',
                '11:00',
                '14:00',
                '16:00'
            ]
        ]);
    }
    
}