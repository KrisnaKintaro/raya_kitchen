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
                $totalRotiDipesan = OrderItem::whereHas('order', function ($q) use ($tglStr) {
                    $q->whereDate('delivery_date', $tglStr)->where('status', '!=', 'dibatalkan');
                })->sum('quantity');

                if (($totalRotiDipesan + $totalRotiCheckout) <= $batasMaksimalDapur) {
                    return $tanggalOtomatis;
                }
            }
            $tanggalOtomatis->addDay();
        }
    }

    /**
     * Ekstrak rentang hari estimasi (ETD) dari API Ekspedisi secara dinamis
     */
    private function parseEtdDays(?string $rawEtd): array
    {
        if (empty($rawEtd)) {
            return ['min' => 1, 'max' => 1, 'label' => '1 Hari'];
        }

        preg_match_all('/\d+/', $rawEtd, $matches);
        $numbers = !empty($matches[0]) ? array_map('intval', $matches[0]) : [];

        if (empty($numbers)) {
            return ['min' => 1, 'max' => 1, 'label' => '1 Hari'];
        }

        $min = max(1, min($numbers));
        $max = max(1, max($numbers));
        $label = ($min === $max) ? "{$max} Hari" : "{$min}-{$max} Hari";

        return [
            'min'   => $min,
            'max'   => $max,
            'label' => $label,
        ];
    }

    /**
     * Format URL bukti pengiriman/pembayaran (mendukung storage lokal & URL eksternal)
     */
    private function formatProofUrl(?string $rawPath, string $folder = 'bukti_pengiriman'): ?string
    {
        if (empty($rawPath)) {
            return null;
        }
        if (str_starts_with($rawPath, 'http://') || str_starts_with($rawPath, 'https://')) {
            return $rawPath;
        }
        $clean = preg_replace('#^/?(storage/)?(' . preg_quote($folder, '#') . '/)?#i', '', trim($rawPath));
        return asset("storage/{$folder}/" . $clean);
    }

    /**
     * Bangun metadata pengiriman & estimasi sampai yang dinamis untuk Riwayat & Nota
     */
    private function buildShippingMeta(Order $order): array
    {
        $courierName = trim((string) $order->courier_name);
        $isInternal = empty($courierName)
            || str_contains(strtolower($courierName), 'internal')
            || (float) $order->shipping_cost <= 0;

        $baseDate = $order->created_at ? Carbon::parse($order->created_at) : now();

        if ($isInternal) {
            $delivDateFormatted = $order->delivery_date
                ? Carbon::parse($order->delivery_date)->locale('id')->isoFormat('D MMMM YYYY')
                : $baseDate->copy()->addDay()->locale('id')->isoFormat('D MMMM YYYY');
            $delivTimeFormatted = $order->delivery_time ? substr($order->delivery_time, 0, 5) . ' WIB' : '15:00 WIB';
            $scheduleLabel = 'Jadwal Pengiriman / Pengambilan';
            $scheduleText  = "{$delivDateFormatted} - {$delivTimeFormatted}";
        } else {
            $scheduleLabel = 'Estimasi Barang Sampai (Kurir Ekspedisi)';
            $etdInfo = $this->parseEtdDays($order->shipping_service);

            // Jika di shipping_service ada angka estimasi dari API, hitung dinamis dari tgl checkout
            if (preg_match('/\d+/', (string) $order->shipping_service)) {
                $minDate = $baseDate->copy()->addDays($etdInfo['min']);
                $maxDate = $baseDate->copy()->addDays($etdInfo['max']);

                if ($etdInfo['min'] === $etdInfo['max']) {
                    $dateRangeStr = $maxDate->locale('id')->isoFormat('D MMMM YYYY');
                } else {
                    $dateRangeStr = $minDate->locale('id')->isoFormat('D MMM YYYY') . ' - ' . $maxDate->locale('id')->isoFormat('D MMM YYYY');
                }
                $scheduleText = "{$dateRangeStr} (Est. {$etdInfo['label']})";
            } elseif ($order->delivery_date) {
                $targetDate = Carbon::parse($order->delivery_date)->locale('id')->isoFormat('D MMMM YYYY');
                $scheduleText = "{$targetDate}";
            } else {
                $scheduleText = 'Mengikuti estimasi kurir ekspedisi';
            }
        }

        $rawArrival = $order->arrival_proof ?? $order->delivery_proof ?? null;
        $rawPayment = $order->payment_proof ?? null;

        return [
            'is_internal'        => $isInternal,
            'courier_name'       => $courierName ?: 'Kurir Internal Raya Kitchen',
            'shipping_service'   => $order->shipping_service ?: ($isInternal ? 'Kurir Toko' : 'Reguler'),
            'shipping_cost'      => (int) round($order->shipping_cost ?? 0),
            'tracking_number'    => $order->tracking_number,
            'courier_status'     => $order->courier_status,
            'courier_manifest'   => is_array($order->courier_manifest) ? $order->courier_manifest : [],
            'schedule_label'     => $scheduleLabel,
            'schedule_text'      => $scheduleText,
            'arrival_proof_url'  => $this->formatProofUrl($rawArrival),
            'payment_proof_url'  => $this->formatProofUrl($rawPayment),
        ];
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
                        $rawEtd = trim(str_ireplace(['hari', 'day', 'days'], '', (string) ($item['etd'] ?? '')));
                        return [[
                            'company'  => strtoupper($item['code']),
                            'type'     => $item['service'],
                            'price'    => $item['cost'],
                            'duration' => $rawEtd !== '' ? $rawEtd : '1-2',
                        ]];
                    }
                    return collect($item['costs'] ?? [])->map(function ($cost) use ($item) {
                        $rawEtd = trim(str_ireplace(['hari', 'day', 'days'], '', (string) ($cost['cost'][0]['etd'] ?? '')));
                        return [
                            'company'  => strtoupper($item['code']),
                            'type'     => $cost['service'],
                            'price'    => $cost['cost'][0]['value'],
                            'duration' => $rawEtd !== '' ? $rawEtd : '1-2',
                        ];
                    });
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
            $courierNameInput = trim((string) $request->input('courier_name', ''));
            $isInternal = ($shippingCost == 0 && (empty($courierNameInput) || str_contains(strtolower($courierNameInput), 'internal')));

            if (!$isInternal) {
                // DINAMIS BERDASARKAN ESTIMASI API KURIR EKSTERNAL (BUKAN HARDCODE H+2)
                $rawEtdSource = $request->input('etd') ?: $request->input('shipping_service');
                $etdParsed = $this->parseEtdDays($rawEtdSource);
                $deliveryDate = now()->addDays($etdParsed['max'])->format('Y-m-d');
                $deliveryTime = null;
            } elseif ($request->input('delivery_date') === 'auto' || empty($request->input('delivery_date'))) {
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

            $shippingMeta = $this->buildShippingMeta($order);

            return array_merge([
                'order_number'           => $order->order_number,
                'status'                 => $oStatus,
                'raw_status'             => $oStatus,
                'display_status'         => $displayStatus,
                'payment_method'         => $order->transaction->payment_method ?? '-',
                'payment_status'         => $pStatus,
                'tipe_bayar'             => $isDP ? 'DP 30%' : 'Lunas',
                'date'                   => $order->created_at->format('d M Y, H:i'),
                'recipient_name'         => $order->recipient_name,
                'recipient_whatsapp'     => $order->recipient_whatsapp,
                'shipping_address'       => $order->shipping_address,
                'delivery_date'          => $order->delivery_date,
                'delivery_time'          => $order->delivery_time,
                'total_amount'           => (int) $order->total_amount,
                'remaining_amount'       => (int) $order->remaining_amount,
                'total_amount_formatted' => 'Rp ' . number_format($order->total_amount, 0, ',', '.'),
                'tagihan_sekarang'       => 'Rp ' . number_format($order->transaction->amount ?? 0, 0, ',', '.'),
                'sisa_tagihan_formatted' => 'Rp ' . number_format($order->remaining_amount, 0, ',', '.'),
                'sisa_tagihan_formatted' => 'Rp ' . number_format($order->remaining_amount, 0, ',', '.'),
                'cancellation_reason'    => $order->cancellation_reason,
                'refund_status'          => $order->refund_status ?? 'none',
                'refund_account_number'  => $order->refund_account_number,
                'refund_proof_url'       => $this->formatProofUrl($order->refund_proof, 'bukti_refund'),
            ], $shippingMeta);
        });

        return response()->json(['status' => 'success', 'data' => $mappedOrders]);
    }

    public function submitRefundAccount(Request $request)
    {
    $validated = $request->validate([
        'order_number' => 'required|string',
        'refund_bank'  => 'required|string|max:100',
        'refund_norek' => 'required|string|max:100',
        'refund_nama'  => 'required|string|max:150',
    ], [
        'refund_bank.required'  => 'Nama Bank atau E-Wallet wajib diisi.',
        'refund_norek.required' => 'Nomor rekening atau nomor tujuan wajib diisi.',
        'refund_nama.required'  => 'Nama pemilik rekening wajib diisi.',
    ]);

    $order = Order::where('order_number', $validated['order_number'])
        ->where('status', 'dibatalkan')
        ->first();

    if (!$order) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Pesanan batal tidak ditemukan.',
        ], 404);
    }

    if ($order->refund_status === 'refunded') {
        return response()->json([
            'status'  => 'error',
            'message' => 'Pengembalian dana untuk pesanan ini sudah selesai diproses.',
        ], 422);
    }

    $rekeningString = trim($validated['refund_bank']) . ' - ' . trim($validated['refund_norek']) . ' - ' . trim($validated['refund_nama']);

    $order->update([
        'refund_account_number' => $rekeningString,
        'refund_status'         => 'pending',
    ]);

    return response()->json([
        'status'  => 'success',
        'message' => 'Nomor rekening pengembalian dana berhasil disimpan! Admin akan memproses refund maksimal 1x24 jam.',
        'data'    => [
            'refund_account_number' => $rekeningString,
            'refund_status'         => 'pending',
        ],
    ]);
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
        $shippingMeta = $this->buildShippingMeta($order);

        $orderArray = $order->toArray();
        $orderArray['shipping_meta']     = $shippingMeta;
        $orderArray['arrival_proof_url'] = $shippingMeta['arrival_proof_url'];
        $orderArray['payment_proof_url'] = $shippingMeta['payment_proof_url'];
        $orderArray['refund_proof_url']  = $this->formatProofUrl($order->refund_proof, 'bukti_refund');

        return response()->json([
            'status' => 'success',
            'data' => [
                'order'   => $orderArray,
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
        $expiredAt = $order->created_at->addMinutes(1440);

        if ($order->status === 'pending' && $pStatus === 'unpaid' && now()->greaterThanOrEqualTo($expiredAt)) {
            $order->update(['status' => 'dibatalkan', 'cancellation_reason' => 'Otomatis dibatalkan: Waktu pembayaran habis (24 jam).']);
            $order->transaction->update(['payment_status' => 'failed']);
            NotificationController::send($order, 'batal_otomatis');
        }

        if ($pStatus === 'paid_dp' && $order->remaining_amount > 0 && !in_array($order->status, ['selesai', 'dibatalkan'])) {
            $this->generatePelunasanInvoice($order);
            $order->load('transaction');
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'order'        => $order,
                'expired_at'   => $expiredAt->toIso8601String(),
                'is_pelunasan' => ($pStatus === 'paid_dp' && $order->remaining_amount > 0),
                'is_lunas'     => ($pStatus === 'paid_full' || ($pStatus !== 'unpaid' && $pStatus !== 'failed' && $order->remaining_amount <= 0)),
                'is_sandbox'   => (bool) env('DUITKU_SANDBOX', true),
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

    public function simulasiBayarSandbox(Request $request, $orderNumber)
    {
        if (!env('DUITKU_SANDBOX', true)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Simulasi klik QRIS hanya aktif pada mode Sandbox.'
            ], 403);
        }

        $order = Order::with('transaction')->where('order_number', $orderNumber)->first();

        if (!$order || !$order->transaction) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data pesanan atau transaksi tidak ditemukan.'
            ], 404);
        }

        if ($order->status === 'dibatalkan') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pesanan sudah dibatalkan sehingga tidak dapat dibayar.'
            ], 422);
        }

        $prevPaymentStatus = strtolower($order->transaction->payment_status ?? 'unpaid');

        if ($prevPaymentStatus === 'paid_full') {
            return response()->json([
                'status'         => 'success',
                'message'        => 'Pesanan ini sudah lunas sebelumnya.',
                'payment_status' => 'paid_full',
                'order_status'   => $order->status,
            ]);
        }

        // Deteksi otomatis apakah ini Pelunasan Sisa DP (70%) atau Pembayaran Awal (Lunas 100% / DP 30%)
        $isPelunasan = ($prevPaymentStatus === 'paid_dp' && (float) $order->remaining_amount > 0);
        $simulatedAmount = $isPelunasan
            ? (int) round($order->remaining_amount)
            : (int) round($order->transaction->amount);

        DB::beginTransaction();
        try {
            if ($isPelunasan || $simulatedAmount >= (int) round($order->total_amount) || (int) round($order->remaining_amount) === 0) {
                $updateOrderData = ['remaining_amount' => 0];
                $normalizedStatus = strtolower(str_replace(' ', '_', $order->status));
                $hasArrivalProof = !empty($order->arrival_proof) || !empty($order->delivery_proof);

                if (in_array($normalizedStatus, ['siap_diambil', 'siap_diantar', 'siap_dikirim', 'dikirim', 'diantar']) && $hasArrivalProof) {
                    $updateOrderData['status'] = 'selesai';
                }

                $order->update($updateOrderData);

                $order->transaction->update([
                    'amount'         => $order->total_amount,
                    'payment_status' => 'paid_full',
                    'paid_at'        => now(),
                ]);

                DB::commit();
                $order->refresh();

                if ($isPelunasan) {
                    NotificationController::send($order, 'pelunasan_berhasil');
                } else {
                    NotificationController::send($order, 'pembayaran_lunas');
                }
            } else {
                $order->transaction->update([
                    'payment_status' => 'paid_dp',
                    'paid_at'        => now(),
                ]);

                DB::commit();
                $order->refresh();
                NotificationController::send($order, 'pembayaran_dp');
            }

            return response()->json([
                'status'         => 'success',
                'message'        => 'Simulasi pembayaran QRIS Sandbox berhasil diproses!',
                'payment_status' => $order->transaction->payment_status,
                'order_status'   => $order->status,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memproses simulasi pembayaran: ' . $e->getMessage(),
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

            $rawMerchantOrderId = $notif->merchantOrderId ?? '';
            $isPelunasanInvoice = str_contains($rawMerchantOrderId, '-LUNAS');
            $cleanOrderId = preg_replace('/(-LUNAS.*|-R\d+)$/', '', $rawMerchantOrderId);

            $order = Order::with('transaction')->where('order_number', $cleanOrderId)->first();

            if ($order && $order->transaction) {
                if ($notif->resultCode == "00") {
                    $prevPaymentStatus = $order->transaction->payment_status;

                    if ($isPelunasanInvoice || $prevPaymentStatus === 'paid_dp' || $notif->amount >= $order->total_amount || $order->remaining_amount == 0) {
                        $updateOrderData = ['remaining_amount' => 0];
                        $normalizedStatus = strtolower(str_replace(' ', '_', $order->status));
                        $hasArrivalProof = !empty($order->arrival_proof) || !empty($order->delivery_proof);

                        if (in_array($normalizedStatus, ['siap_diambil', 'siap_diantar', 'siap_dikirim', 'dikirim', 'diantar']) && $hasArrivalProof) {
                            $updateOrderData['status'] = 'selesai';
                        }

                        $order->update($updateOrderData);

                        $order->transaction->update([
                            'amount'         => $order->total_amount,
                            'payment_status' => 'paid_full',
                            'paid_at'        => now()
                        ]);

                        $order->refresh();

                        if ($prevPaymentStatus === 'paid_dp' || $isPelunasanInvoice) {
                            NotificationController::send($order, 'pelunasan_berhasil');
                        } else {
                            NotificationController::send($order, 'pembayaran_lunas');
                        }
                    } else {
                        $order->transaction->update([
                            'payment_status' => 'paid_dp',
                            'paid_at'        => now()
                        ]);
                        $order->refresh();
                        NotificationController::send($order, 'pembayaran_dp');
                    }
                } else if ($notif->resultCode == "01") {
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

    private function generatePelunasanInvoice(Order $order): array
    {
        $merchantCode = env('DUITKU_MERCHANT_CODE');
        $merchantKey = env('DUITKU_MERCHANT_KEY');

        $duitkuConfig = new \Duitku\Config($merchantKey, $merchantCode);
        $duitkuConfig->setSandboxMode(true);
        $duitkuConfig->setSanitizedMode(true);
        $duitkuConfig->setDuitkuLogs(false);

        $pelunasanOrderId = $order->order_number . '-LUNAS-' . rand(10, 99);

        $params = [
            'paymentAmount'   => (int) $order->remaining_amount,
            'paymentMethod'   => "SP",
            'merchantOrderId' => $pelunasanOrderId,
            'productDetails'  => 'Pelunasan Pesanan ' . $order->order_number,
            'email'           => 'customer@rayakitchen.com',
            'phoneNumber'     => $order->recipient_whatsapp,
            'customerVaName'  => $order->recipient_name,
            'callbackUrl'     => url('/api/duitku/callback'),
            'returnUrl'       => url('/riwayat-pesanan?success=true'),
            'expiryPeriod'    => 1440
        ];

        $responseDuitkuApi = \Duitku\Api::createInvoice($params, $duitkuConfig);
        $result = json_decode($responseDuitkuApi, true);

        if (isset($result['statusCode']) && $result['statusCode'] == '00') {
            // Jangan ubah 'amount' ke total_amount sebelum pelunasan benar-benar dibayar
            $order->transaction->update([
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

        if ($order->remaining_amount <= 0 || $pStatus === 'paid_full') {
            return response()->json(['status' => 'error', 'message' => 'Pesanan ini sudah lunas cuy.'], 400);
        }

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
            if (!$order->delivery_time) continue;
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
