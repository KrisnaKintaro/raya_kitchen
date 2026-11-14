<?php

namespace App\Http\Controllers\Api\Pembeli;

use App\Http\Controllers\Api\Pembeli\NotificationController;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CompanyProfile;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StoreHoliday;
use App\Models\StoreSetting;
use App\Models\Transaction;
use App\Models\UserAddress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    private function getValidProductionDate($totalRotiCheckout)
    {
        $setting = StoreSetting::first();
        $batasMaksimalDapur = $setting->max_qty_per_day ?? 60;
        $tanggalOtomatis = now()->addDay();

        while (true) {
            $tglStr = $tanggalOtomatis->format('Y-m-d');
            $isLibur = StoreHoliday::whereDate('holiday_date', $tglStr)->exists();

            if (!$isLibur) {
                $totalRotiDipesan = OrderItem::whereHas('order', function($q) use ($tglStr) {
                    $q->whereDate('delivery_date', $tglStr)->where('status', '!=', 'dibatalkan');
                })->sum('quantity');

                if (($totalRotiDipesan + $totalRotiCheckout) <= $batasMaksimalDapur) {
                    return $tanggalOtomatis;
                }
            }
            $tanggalOtomatis->addDay();
        }
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
        } catch (\Exception $e) {
            Log::error('Gagal fetch RajaOngkir ID: ' . $e->getMessage());
        }

        return null;
    }

    public function cekOngkir(Request $request)
    {
        $alamat = UserAddress::find($request->address_id);
        $dapur = CompanyProfile::first();
        $setting = StoreSetting::first();

        $max_jarak = $setting->free_delivery_max_distance ?? 10;

        if (!$alamat) {
            return response()->json(['status' => 'error', 'message' => 'Alamat tidak ditemukan.'], 404);
        }

        if ($alamat->distance_to_store !== null && $alamat->distance_to_store <= $max_jarak) {
            return response()->json([
                'status' => 'success',
                'is_internal' => true,
                'courier_name' => 'Kurir Internal Raya Kitchen',
                'shipping_service' => 'Gratis Ongkir (' . $alamat->distance_to_store . ' KM)',
                'shipping_cost' => 0
            ]);
        }

        if (!$alamat->rajaongkir_destination_id || !$dapur->rajaongkir_destination_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jarak di luar jangkauan Kurir Internal. ID Tujuan wilayah belum diset untuk Ekspedisi. Silakan edit dan simpan ulang alamat Anda.'
            ], 400);
        }

        $activeCouriers = $setting->active_couriers ?? 'jne,sicepat,jnt';

        $userId = $request->user()->id ?? Auth::id();
        $cartItems = Cart::with('productVariant')->where('user_id', $userId)->get();

        $totalBeratGram = 0;
        foreach ($cartItems as $item) {
            $beratSatuan = $item->productVariant->weight ?? 500;
            $totalBeratGram += ($beratSatuan * $item->quantity);
        }

        if ($totalBeratGram < 1000) {
            $totalBeratGram = 1000;
        }

        try {
            $validCouriers = ['jne','sicepat','ide','sap','jnt','ninja','tiki','lion','anteraja','pos','ncs','rex','rpx','sentral','star','wahana','spx'];

            $apiCouriers = collect(explode(',', $activeCouriers))
                ->map(fn($c) => trim(strtolower($c)))
                ->filter(fn($c) => in_array($c, $validCouriers))
                ->unique()
                ->implode(':');

            if (empty($apiCouriers)) {
                return response()->json(['status' => 'error', 'message' => 'Layanan kurir ekspedisi tidak tersedia saat ini.'], 400);
            }

            $response = Http::withHeaders([
                'key' => env('RAJAONGKIR_API_KEY'),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])->retry(3, 1500)->asForm()->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                'origin' => $dapur->rajaongkir_destination_id,
                'destination' => $alamat->rajaongkir_destination_id,
                'weight' => $totalBeratGram,
                'courier' => $apiCouriers
            ]);

            if ($response->successful() && isset($response->json()['data'])) {
                $rates = collect($response->json()['data'])->flatMap(function ($item) {
                    if (isset($item['service'])) {
                        return [[
                            'company'  => strtoupper($item['code']),
                            'type'     => $item['service'],
                            'price'    => $item['cost'],
                            'duration' => $item['etd'] ?? '2-3',
                        ]];
                    }

                    return collect($item['costs'] ?? [])->map(fn($cost) => [
                        'company'  => strtoupper($item['code']),
                        'type'     => $cost['service'],
                        'price'    => $cost['cost'][0]['value'],
                        'duration' => $cost['cost'][0]['etd'] ?? '2-3',
                    ]);
                })->filter(fn($r) => is_numeric($r['price']) && strtolower($r['company']) !== 'pemilik_toko');

                if ($rates->isEmpty()) {
                    return response()->json(['status' => 'error', 'message' => 'Layanan kurir tidak tersedia untuk tujuan ini.'], 400);
                }

                return response()->json([
                    'status' => 'success',
                    'is_internal' => false,
                    'rates' => $rates->values()->all()
                ]);
            }

            $errorMessage = $response->json()['meta']['message'] ?? 'API RajaOngkir gagal mengembalikan tarif.';
            return response()->json(['status' => 'error', 'message' => $errorMessage], 400);

        } catch (\Exception $e) {
            Log::error('RajaOngkir Exception: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal terhubung ke RajaOngkir: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'payment_method' => 'required|in:transfer_full,transfer_dp,cod'
        ]);

        $userId = $request->user()->id ?? Auth::id();
        $cartItems = Cart::with('productVariant')->where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Keranjang kosong!'], 400);
        }

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $totalRotiCheckout = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item->productVariant->price * $item->quantity;
                $totalRotiCheckout += $item->quantity;
            }

            $shippingCost = (int) round($request->input('shipping_cost', 0));
            $grandTotal = (int) round($totalAmount + $shippingCost);
            $dpAmount = (int) round($grandTotal * 0.3);

            $pilihanBayar = $request->input('payment_method');
            $remainingAmount = ($pilihanBayar === 'transfer_full') ? 0 : ($grandTotal - $dpAmount);
            $amountToPay = ($pilihanBayar === 'transfer_full') ? $grandTotal : $dpAmount;

            $orderNumber = 'RK-' . date('Ymd') . '-' . rand(1000, 9999);
            $isInternal = ($shippingCost == 0);

            if ($request->input('delivery_date') === 'auto' || (!$isInternal && $shippingCost > 0)) {
                $tglDapurKosong = $this->getValidProductionDate($totalRotiCheckout);
                $deliveryDate = $tglDapurKosong->format('Y-m-d');
                $deliveryTime = '15:00:00';
            } else {
                $deliveryDate = $request->input('delivery_date');
                $deliveryTime = $request->input('delivery_time');
            }

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
                'order_source' => $request->input('order_source', 'web'),
            ]);

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

            $tipeTransaksi = ($pilihanBayar === 'cod') ? 'cod' : 'transfer';

            $transaction = Transaction::create([
                'order_id' => $order->id,
                'amount' => $amountToPay,
                'payment_method' => $tipeTransaksi,
                'payment_status' => 'unpaid'
            ]);

            Cart::where('user_id', $userId)->delete();

            $merchantCode = env('DUITKU_MERCHANT_CODE');
            $merchantKey = env('DUITKU_MERCHANT_KEY');

            $duitkuConfig = new \Duitku\Config($merchantKey, $merchantCode);
            $duitkuConfig->setSandboxMode(true);
            $duitkuConfig->setSanitizedMode(true);
            $duitkuConfig->setDuitkuLogs(false);

            $params = [
                'paymentAmount'     => $amountToPay,
                'paymentMethod'     => "SP",
                'merchantOrderId'   => $orderNumber,
                'productDetails'    => 'Pembayaran Pesanan ' . $orderNumber,
                'email'             => 'customer@rayakitchen.com',
                'phoneNumber'       => $request->input('phone'),
                'customerVaName'    => $request->input('name'),
                'callbackUrl'       => url('/api/duitku/callback'),
                'returnUrl'         => url('/riwayat-pesanan?success=true'),
                'expiryPeriod'      => 1440
            ];

            try {
                $responseDuitkuApi = \Duitku\Api::createInvoice($params, $duitkuConfig);
                $result = json_decode($responseDuitkuApi, true);

                if (isset($result['statusCode']) && $result['statusCode'] == '00') {
                    $transaction->update([
                        'reference' => $result['reference'] ?? null,
                        'qr_string' => $result['qrString'] ?? null
                    ]);

                    DB::commit();

                    return response()->json([
                        'status' => 'success',
                        'data' => [
                            'order_number' => $orderNumber,
                            'amount_to_pay' => $amountToPay,
                            'redirect_url' => url('/pembayaran/' . $orderNumber),
                            'nota_url' => url('/nota?order_number=' . $orderNumber)
                        ]
                    ], 200);
                } else {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Duitku Error: ' . ($result['statusMessage'] ?? '')], 500);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Exception Duitku: ' . $e->getMessage()], 500);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal memproses pesanan: ' . $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        $userId = $request->user()->id ?? Auth::id();
        $orders = Order::with('transaction')->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($orders as $order) {
            // Hanya batalkan otomatis jika status pembayaran masih UNPAID lewat 24 jam
            $expiredAt = $order->created_at->addMinutes(1440);
            if ($order->status === 'pending' && $order->transaction && $order->transaction->payment_status === 'unpaid' && now()->greaterThanOrEqualTo($expiredAt)) {
                $order->update(['status' => 'dibatalkan', 'cancellation_reason' => 'Otomatis dibatalkan: Waktu pembayaran QRIS habis (24 jam).']);
                $order->transaction->update(['payment_status' => 'failed']);
                NotificationController::send($order, 'batal_otomatis');
            }
        }

        $mappedOrders = $orders->map(function ($order) {
            $pStatus = strtolower($order->transaction->payment_status ?? 'unpaid');
            $rawStatus = strtolower(str_replace(' ', '_', $order->status ?? 'pending'));

            // Normalisasi variasi penamaan status siap diantar / siap diambil / dikirim
            if (in_array($rawStatus, ['siap_diambil', 'siap_diantar', 'siap_dikirim', 'dikirim', 'diantar'])) {
                $oStatus = 'siap_diambil';
            } else {
                $oStatus = $rawStatus;
            }

            $isDP = ($order->remaining_amount > 0) || $pStatus === 'paid_dp';

            if ($oStatus === 'dibatalkan') {
                $displayStatus = 'DIBATALKAN';
            } elseif ($pStatus === 'unpaid' || $pStatus === 'failed') {
                $displayStatus = 'BELUM DIBAYAR';
            } elseif ($oStatus === 'pending') {
                $displayStatus = 'DALAM ANTREAN';
            } elseif ($oStatus === 'diproses') {
                $displayStatus = 'SEDANG DIPROSES';
            } elseif ($oStatus === 'siap_diambil') {
                $displayStatus = 'SIAP DIANTAR/DIAMBIL';
            } elseif ($oStatus === 'selesai') {
                $displayStatus = 'SELESAI';
            } else {
                $displayStatus = strtoupper(str_replace('_', ' ', $order->status));
            }

            return [
                'order_number' => $order->order_number,
                'status' => $oStatus, // gunakan format snake_case konsisten agar filter & kondisi di web/WA cocok 100%
                'raw_status' => $oStatus,
                'display_status' => $displayStatus,
                'payment_method' => $order->transaction->payment_method ?? '-',
                'payment_status' => $pStatus,
                'tipe_bayar' => $isDP ? 'DP 30%' : 'Lunas',
                'date' => $order->created_at->format('d M Y, H:i'),
                'recipient_name' => $order->recipient_name,
                'recipient_whatsapp' => $order->recipient_whatsapp,
                'shipping_address' => $order->shipping_address,
                'total_amount' => (int) $order->total_amount,
                'remaining_amount' => (int) $order->remaining_amount,
                'total_amount_formatted' => 'Rp ' . number_format($order->total_amount, 0, ',', '.'),
                'tagihan_sekarang' => 'Rp ' . number_format($order->transaction->amount ?? 0, 0, ',', '.'),
                'sisa_tagihan_formatted' => 'Rp ' . number_format($order->remaining_amount, 0, ',', '.'),
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
            'landmark'       => 'nullable|string',
            'latitude'       => 'nullable|numeric',
            'longitude'      => 'nullable|numeric',
        ]);

        $user = $request->user() ?? Auth::user();
        $isPrimary = $user->addresses()->doesntExist();

        $roId = $this->getRajaOngkirId($request->district, $request->city);

        $dapur = CompanyProfile::first();
        $jarak = 0;

        if ($dapur && $dapur->latitude && $dapur->longitude && $request->filled('latitude') && $request->filled('longitude')) {
            $jarak = $this->hitungJarak($dapur->latitude, $dapur->longitude, $request->input('latitude'), $request->input('longitude'));
        }

        $address = $user->addresses()->create(array_merge($validated, [
            'recipient_name'     => $user->name,
            'recipient_whatsapp' => $user->whatsapp_number,
            'is_primary'         => $isPrimary,
            'rajaongkir_destination_id' => $roId,
            'distance_to_store'  => $jarak,
        ]));

        $prefix = $isPrimary ? '[Utama] ' : '';
        $teksLengkap = "{$prefix}{$address->label} - {$address->street_address}, {$address->district}, {$address->city}, {$address->province} {$address->postal_code}";

        return response()->json([
            'status' => 'success',
            'data'   => [
                'value' => $teksLengkap,
                'text'  => $teksLengkap,
                'id'    => $address->id
            ]
        ]);
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
        $distance = $earthRadius * $c;

        return round($distance, 2);
    }

    public function showPembayaran($orderNumber)
    {
        $order = Order::with('transaction')->where('order_number', $orderNumber)->first();

        if (!$order || !$order->transaction) {
            return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        $pStatus = strtolower($order->transaction->payment_status);

        // 1. Jika masih UNPAID, batas waktunya 24 jam (1440 menit) sejak order dibuat
        $expiredAt = $order->created_at->addMinutes(1440);

        if ($order->status === 'pending' && $pStatus === 'unpaid' && now()->greaterThanOrEqualTo($expiredAt)) {
            $order->update(['status' => 'dibatalkan', 'cancellation_reason' => 'Otomatis dibatalkan: Waktu pembayaran habis (24 jam).']);
            $order->transaction->update(['payment_status' => 'failed']);
            NotificationController::send($order, 'batal_otomatis');
        }

        // 2. Jika sudah PAID_DP (mau bayar sisa tagihan 70%) dan belum pernah generate QRIS pelunasan,
        // otomatis buatkan QRIS pelunasan supaya QR yang tampil adalah tagihan sisa 70%
        if ($pStatus === 'paid_dp' && $order->remaining_amount > 0 && $order->transaction->amount < $order->total_amount && !in_array($order->status, ['selesai', 'dibatalkan'])) {
            $this->generatePelunasanInvoice($order);
            $order->load('transaction');
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'order' => $order,
                'expired_at' => $expiredAt->toIso8601String(),
                'is_pelunasan' => ($pStatus === 'paid_dp' && $order->remaining_amount > 0),
                'is_lunas' => ($pStatus === 'paid_full' || ($pStatus !== 'unpaid' && $pStatus !== 'failed' && $order->remaining_amount <= 0)),
            ]
        ]);
    }

    public function cekStatusPembayaran($orderNumber)
    {
        $order = Order::with('transaction')->where('order_number', $orderNumber)->first();

        if (!$order || !$order->transaction) {
            return response()->json(['status' => 'error', 'message' => 'Order tidak ditemukan'], 404);
        }

        $expiredAt = $order->created_at->addMinutes(1440);

        // Hanya batalkan otomatis jika masih UNPAID (DP yang mau dilunasi tidak terkena batas 24 jam awal)
        if ($order->status === 'pending' && $order->transaction->payment_status === 'unpaid' && now()->greaterThanOrEqualTo($expiredAt)) {
            $order->update(['status' => 'dibatalkan', 'cancellation_reason' => 'Otomatis dibatalkan: Waktu pembayaran habis (24 jam).']);
            $order->transaction->update(['payment_status' => 'failed']);
            NotificationController::send($order, 'batal_otomatis');
            return response()->json(['status' => 'success', 'payment_status' => 'failed', 'order_status' => 'dibatalkan']);
        }

        return response()->json([
            'status' => 'success',
            'payment_status' => $order->transaction->payment_status,
            'order_status' => $order->status,
            'remaining_amount' => (int) $order->remaining_amount
        ]);
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

        $userId = $request->user()?->id ?? Auth::id();

        $query = Order::with('transaction')->where('order_number', $request->order_number);
        if ($userId) {
            $query->where('user_id', $userId);
        }
        $order = $query->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json(['status' => 'error', 'message' => 'Pesanan sudah diproses atau dibatalkan, sehingga tidak bisa dibatalkan lagi.'], 400);
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

            $rawMerchantOrderId = $notif->merchantOrderId ?? '';
            $isPelunasanInvoice = str_contains($rawMerchantOrderId, '-LUNAS');
            $cleanOrderId = preg_replace('/(-LUNAS.*|-R\d+)$/', '', $rawMerchantOrderId);

            $order = Order::with('transaction')->where('order_number', $cleanOrderId)->first();

            if ($order && $order->transaction) {
                if ($notif->resultCode == "00") {
                    $prevPaymentStatus = $order->transaction->payment_status;

                    // Cek apakah ini pelunasan DP atau pembayaran lunas 100% di awal
                    if ($isPelunasanInvoice || $prevPaymentStatus === 'paid_dp' || $notif->amount >= $order->total_amount || $order->remaining_amount == 0) {
                        $updateOrderData = ['remaining_amount' => 0];

                        // Jika status pesanan saat ini sudah siap_diambil / siap_diantar, otomatis ubah jadi selesai!
                        $normalizedStatus = strtolower(str_replace(' ', '_', $order->status));
                        if (in_array($normalizedStatus, ['siap_diambil', 'siap_diantar', 'siap_dikirim', 'dikirim', 'diantar'])) {
                            $updateOrderData['status'] = 'selesai';
                        }

                        $order->update($updateOrderData);
                        $order->transaction->update([
                            'payment_status' => 'paid_full',
                            'paid_at' => now()
                        ]);

                        $order->refresh();

                        // Kirim notifikasi WA & Web via Satpam NotificationController
                        if ($prevPaymentStatus === 'paid_dp' || $isPelunasanInvoice) {
                            NotificationController::send($order, 'pelunasan_berhasil');
                        } else {
                            NotificationController::send($order, 'pembayaran_lunas');
                        }
                    } else {
                        // Pembayaran DP 30% di awal
                        $order->transaction->update([
                            'payment_status' => 'paid_dp',
                            'paid_at' => now()
                        ]);

                        $order->refresh();
                        NotificationController::send($order, 'pembayaran_dp');
                    }
                } else if ($notif->resultCode == "01") {
                    // Jangan timpa jadi failed kalau sebelumnya sudah paid_dp (agar status DP tetap aman)
                    if ($order->transaction->payment_status === 'unpaid') {
                        $order->transaction->update(['payment_status' => 'failed']);
                    }
                    NotificationController::send($order, 'pembayaran_gagal');
                }
            }

            return response()->json(['message' => 'Callback Processed']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Helper internal untuk membuat invoice QRIS Pelunasan 70% ke Duitku
     */
    private function generatePelunasanInvoice(Order $order): array
    {
        $merchantCode = env('DUITKU_MERCHANT_CODE');
        $merchantKey = env('DUITKU_MERCHANT_KEY');

        $duitkuConfig = new \Duitku\Config($merchantKey, $merchantCode);
        $duitkuConfig->setSandboxMode(true);
        $duitkuConfig->setSanitizedMode(true);
        $duitkuConfig->setDuitkuLogs(false);

        // Gunakan suffix unik bila user klik bayar sisaan berkali-kali agar tidak ditolak duplicate order ID oleh Duitku
        $pelunasanOrderId = $order->order_number . '-LUNAS-' . rand(10, 99);

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
            'expiryPeriod'      => 1440
        ];

        $responseDuitkuApi = \Duitku\Api::createInvoice($params, $duitkuConfig);
        $result = json_decode($responseDuitkuApi, true);

        if (isset($result['statusCode']) && $result['statusCode'] == '00') {
            $order->transaction->update([
                'amount' => $order->total_amount,
                'reference' => $result['reference'] ?? null,
                'qr_string' => $result['qrString'] ?? null
            ]);

            return ['success' => true, 'result' => $result];
        }

        return ['success' => false, 'message' => $result['statusMessage'] ?? 'Unknown Error'];
    }

    public function payRemaining(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string'
        ]);

        $order = Order::with('transaction')
            ->where('order_number', $request->order_number)
            ->first();

        if (!$order || !$order->transaction) {
            return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($order->status === 'dibatalkan') {
            return response()->json(['status' => 'error', 'message' => 'Pesanan ini sudah dibatalkan.'], 400);
        }

        if ($order->status === 'selesai') {
            return response()->json(['status' => 'error', 'message' => 'Pesanan ini sudah selesai.'], 400);
        }

        $pStatus = $order->transaction->payment_status;

        // KASUS 1: Pesanan masih UNPAID (Bayar Ulang / Ambil Link QRIS Awal)
        if ($pStatus === 'unpaid' || $pStatus === 'failed') {
            if (!empty($order->transaction->qr_string) && $pStatus === 'unpaid') {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Silakan lanjutkan pembayaran QRIS untuk pesanan ini.',
                    'data' => [
                        'amount_to_pay' => (int) $order->transaction->amount,
                        'redirect_url' => url('/pembayaran/' . $order->order_number)
                    ]
                ], 200);
            }

            $merchantCode = env('DUITKU_MERCHANT_CODE');
            $merchantKey = env('DUITKU_MERCHANT_KEY');
            $duitkuConfig = new \Duitku\Config($merchantKey, $merchantCode);
            $duitkuConfig->setSandboxMode(true);
            $duitkuConfig->setSanitizedMode(true);
            $duitkuConfig->setDuitkuLogs(false);

            $retryOrderId = $order->order_number . '-R' . rand(10, 99);
            $params = [
                'paymentAmount'   => (int) $order->transaction->amount,
                'paymentMethod'   => "SP",
                'merchantOrderId' => $retryOrderId,
                'productDetails'  => 'Pembayaran Pesanan ' . $order->order_number,
                'email'           => 'customer@rayakitchen.com',
                'phoneNumber'     => $order->recipient_whatsapp,
                'customerVaName'  => $order->recipient_name,
                'callbackUrl'     => url('/api/duitku/callback'),
                'returnUrl'       => url('/riwayat-pesanan?success=true'),
                'expiryPeriod'    => 1440
            ];

            try {
                $responseDuitkuApi = \Duitku\Api::createInvoice($params, $duitkuConfig);
                $result = json_decode($responseDuitkuApi, true);

                if (isset($result['statusCode']) && $result['statusCode'] == '00') {
                    $order->transaction->update([
                        'payment_status' => 'unpaid',
                        'reference' => $result['reference'] ?? null,
                        'qr_string' => $result['qrString'] ?? null
                    ]);

                    return response()->json([
                        'status' => 'success',
                        'message' => 'QRIS Pembayaran berhasil dibuat ulang!',
                        'data' => [
                            'amount_to_pay' => (int) $order->transaction->amount,
                            'redirect_url' => url('/pembayaran/' . $order->order_number)
                        ]
                    ], 200);
                }
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Exception Duitku: ' . $e->getMessage()], 500);
            }
        }

        // KASUS 2: Pesanan sudah LUNAS
        if ($order->remaining_amount <= 0 || $pStatus === 'paid_full') {
            return response()->json(['status' => 'error', 'message' => 'Pesanan ini sudah lunas cuy.'], 400);
        }

        // KASUS 3: Pesanan DP (paid_dp) -> Buat QRIS Pelunasan 70%
        try {
            $gen = $this->generatePelunasanInvoice($order);
            if ($gen['success']) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'QRIS Pelunasan berhasil dibuat!',
                    'data' => [
                        'amount_to_pay' => (int) $order->remaining_amount,
                        'redirect_url' => url('/pembayaran/' . $order->order_number)
                    ]
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal terhubung ke Duitku: ' . ($gen['message'] ?? 'Unknown Error')
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

        $userId = $request->user()?->id ?? Auth::id();
        if (!$userId) {
            return response()->json(['status' => 'error', 'message' => 'Silakan login terlebih dahulu.']);
        }

        $setting = StoreSetting::first();
        $maxQtyPerDay = $setting->max_qty_per_day ?? 60;

        $cartQty = Cart::where('user_id', $userId)->sum('quantity');

        if ($cartQty == 0) {
            return response()->json(['status' => 'error', 'message' => 'Keranjang kosong.']);
        }

        $orders = Order::with('items')
            ->whereMonth('delivery_date', $bulan)
            ->whereYear('delivery_date', $tahun)
            ->where('status', '!=', 'dibatalkan')
            ->get();

        $rekapHarian = [];
        foreach ($orders as $order) {
            $tgl = $order->delivery_date;
            if (!isset($rekapHarian[$tgl])) $rekapHarian[$tgl] = 0;
            foreach ($order->items as $item) {
                $rekapHarian[$tgl] += $item->quantity;
            }
        }

        $holidays = StoreHoliday::whereMonth('holiday_date', $bulan)
            ->whereYear('holiday_date', $tahun)
            ->pluck('holiday_date')->toArray();

        $heatmap = [];
        $daysInMonth = Carbon::create($tahun, $bulan)->daysInMonth;
        $besok = Carbon::tomorrow()->format('Y-m-d');

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dateStr = sprintf("%04d-%02d-%02d", $tahun, $bulan, $i);

            if ($dateStr < $besok) {
                $heatmap[$dateStr] = 'past';
                continue;
            }

            if (in_array($dateStr, $holidays)) {
                $heatmap[$dateStr] = 'full';
                continue;
            }

            $totalQtyHariIni = $rekapHarian[$dateStr] ?? 0;

            if (($totalQtyHariIni + $cartQty) > $maxQtyPerDay) {
                $heatmap[$dateStr] = 'full';
            } elseif ($totalQtyHariIni > ($maxQtyPerDay * 0.7)) {
                $heatmap[$dateStr] = 'med';
            } else {
                $heatmap[$dateStr] = 'low';
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $heatmap
        ]);
    }

    public function getAvailableTimes(Request $request)
    {
        $tanggalStr = $request->query('tanggal');
        if (!$tanggalStr) {
            return response()->json(['status' => 'error', 'data' => []]);
        }

        $tanggal = Carbon::parse($tanggalStr);

        $setting = StoreSetting::first();
        $maxOrdersPerHour = $setting->max_orders_per_hour ?? 5;
        $startHourStr = $setting->operational_time_start ?? '08:00:00';
        $endHourStr = $setting->operational_time_end ?? '17:00:00';

        $startHour = (int) Carbon::parse($startHourStr)->format('H');
        $endHour = (int) Carbon::parse($endHourStr)->format('H');

        $jamOperasional = [];
        for ($h = $startHour; $h <= $endHour; $h++) {
            $jamOperasional[] = sprintf("%02d:00", $h);
        }

        $nowPlus24 = Carbon::now()->addHours(24);

        $orders = Order::whereDate('delivery_date', $tanggal->format('Y-m-d'))
            ->where('status', '!=', 'dibatalkan')
            ->get();

        $rekapJam = [];
        foreach ($orders as $order) {
            $jam = Carbon::parse($order->delivery_time)->format('H:i');
            if (!isset($rekapJam[$jam])) $rekapJam[$jam] = 0;
            $rekapJam[$jam]++;
        }

        $availableTimes = [];
        foreach ($jamOperasional as $jam) {
            $jamLengkap = Carbon::parse($tanggal->format('Y-m-d') . ' ' . $jam);

            if ($jamLengkap->lessThan($nowPlus24)) {
                continue;
            }

            $terisi = $rekapJam[$jam] ?? 0;
            if ($terisi >= $maxOrdersPerHour) {
                continue;
            }

            $availableTimes[] = $jam;
        }

        return response()->json([
            'status' => 'success',
            'data' => $availableTimes
        ]);
    }
}