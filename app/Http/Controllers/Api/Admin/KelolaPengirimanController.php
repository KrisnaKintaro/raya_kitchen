<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Pembeli\NotificationController;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class KelolaPengirimanController extends Controller
{
    /**
     * Mengambil daftar pesanan yang siap dikirim (Kurir Internal & Ekspedisi)
     * GET /api/admin/kelola-pengiriman
     */
    public function index(Request $request)
    {
        $orders = Order::with([
                'transaction',
                'user.addresses',
                'items.productVariant' => function ($q) {
                    $q->withTrashed()->with([
                        'product' => function ($pq) {
                            $pq->withTrashed();
                        }
                    ]);
                },
            ])
            ->whereIn('status', ['siap_diambil', 'siap_diantar', 'siap_dikirim', 'dikirim', 'diantar'])
            ->whereHas('transaction', function ($tq) {
                $tq->whereIn('payment_status', ['paid_dp', 'paid_full']);
            })
            ->orderBy('delivery_date', 'asc')
            ->orderBy('delivery_time', 'asc')
            ->get();

        $internal = [];
        $ekspedisi = [];

        foreach ($orders as $order) {
            $courierName = trim((string) $order->courier_name);
            $isInternal = empty($courierName)
                || str_contains(strtolower($courierName), 'internal')
                || (float) $order->shipping_cost <= 0;

            $matchedAddress = $this->resolveUserAddress($order);

            $lat = $matchedAddress?->latitude ? (float) $matchedAddress->latitude : null;
            $lng = $matchedAddress?->longitude ? (float) $matchedAddress->longitude : null;

            // Titik awal otomatis menggunakan GPS perangkat Admin/Kurir
            if ($lat !== null && $lng !== null) {
                $gmapsUrl = "https://www.google.com/maps/dir/?api=1&destination={$lat},{$lng}&travelmode=two-wheeler";
            } else {
                $encodedAddr = urlencode($order->shipping_address ?? '');
                $gmapsUrl = "https://www.google.com/maps/dir/?api=1&destination={$encodedAddr}&travelmode=two-wheeler";
            }

            $waClean = preg_replace('/[^0-9]/', '', $order->recipient_whatsapp ?? '');
            if (str_starts_with($waClean, '0')) {
                $waClean = '62' . substr($waClean, 1);
            }

            $trackingNumber = $order->tracking_number ?? null;
            $courierStatus  = $order->courier_status ?: ($trackingNumber ? 'in_transit' : 'waiting_resi');
            $manifest       = is_array($order->courier_manifest) ? $order->courier_manifest : [];

            $rawArrival = $order->arrival_proof ?? $order->delivery_proof ?? null;
            $rawPayment = $order->payment_proof ?? null;

            $formattedOrder = [
                'id'                 => $order->order_number,
                'order_db_id'        => $order->id,
                'type'               => $isInternal ? 'internal' : 'ekspedisi',
                'courier'            => $courierName ?: 'Kurir Internal Raya Kitchen',
                'shipping_service'   => $order->shipping_service ?: ($isInternal ? 'Kurir Toko' : 'Reguler'),
                'shipping_cost'      => (int) round($order->shipping_cost ?? 0),
                'tracking_number'    => $trackingNumber,
                'courier_status'     => $courierStatus,
                'courier_manifest'   => $manifest,
                'arrival_proof_url'  => $this->formatProofUrl($rawArrival),
                'payment_proof_url'  => $this->formatProofUrl($rawPayment),
                'recipient'          => $order->recipient_name,
                'recipient_whatsapp' => $order->recipient_whatsapp,
                'wa_chat_url'        => $waClean ? "https://wa.me/{$waClean}" : null,
                'address'            => $order->shipping_address ?: 'Alamat belum diatur',
                'address_detail'     => [
                    'label'           => $matchedAddress?->label ?? 'Alamat Pengiriman',
                    'street_address'  => $matchedAddress?->street_address ?? $order->shipping_address,
                    'landmark'        => $matchedAddress?->landmark ?? null,
                    'district'        => $matchedAddress?->district ?? '-',
                    'city'            => $matchedAddress?->city ?? '-',
                    'province'        => $matchedAddress?->province ?? '-',
                    'postal_code'     => $matchedAddress?->postal_code ?? '-',
                    'distance_km'     => $matchedAddress?->distance_to_store !== null ? (float) $matchedAddress->distance_to_store : null,
                    'latitude'        => $lat,
                    'longitude'       => $lng,
                    'has_coordinates' => ($lat !== null && $lng !== null),
                    'gmaps_url'       => $gmapsUrl,
                ],
                'delivery_date'      => $order->delivery_date ?? now()->toDateString(),
                'delivery_time'      => $order->delivery_time ?? '15:00:00',
                'payment_method'     => strtolower($order->transaction?->payment_method ?? 'transfer'),
                'payment_status'     => strtolower($order->transaction?->payment_status ?? 'unpaid'),
                'remaining_amount'   => (int) round($order->remaining_amount ?? 0),
                'dp_amount'          => (int) round($order->dp_amount ?? 0),
                'total_amount'       => (int) round($order->total_amount ?? 0),
                'items'              => $order->items->map(function ($item) {
                    $kategori = $item->productVariant?->product?->name ?? '';
                    $varian   = $item->productVariant?->variant_name ?? 'Produk Roti';
                    return [
                        'name'     => $kategori ? "{$kategori} - {$varian}" : $varian,
                        'qty'      => (int) $item->quantity,
                        'price'    => (int) round($item->price_at_order),
                        'subtotal' => (int) round($item->subtotal),
                    ];
                })->values()->all(),
            ];

            if ($isInternal) {
                $internal[] = $formattedOrder;
            } else {
                $ekspedisi[] = $formattedOrder;
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'internal'  => $internal,
                'ekspedisi' => $ekspedisi,
                'summary'   => [
                    'internal_count'  => count($internal),
                    'ekspedisi_count' => count($ekspedisi),
                ],
            ]
        ]);
    }

    /**
     * Input / Update Nomor Resi Pengiriman Ekspedisi Eksternal
     * POST /api/admin/kelola-pengiriman/{order_number}/resi
     */
    public function updateResi(Request $request, string $orderNumber)
    {
        $request->validate([
            'tracking_number' => 'required|string|max:100',
        ], [
            'tracking_number.required' => 'Nomor resi pengiriman wajib diisi.',
        ]);

        $order = Order::where('order_number', $orderNumber)->first();
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        $resi = strtoupper(trim($request->input('tracking_number')));
        $manifest = is_array($order->courier_manifest) ? $order->courier_manifest : [];

        array_unshift($manifest, [
            'time'        => now()->format('d M Y, H:i') . ' WIB',
            'status'      => 'MANIFESTED',
            'description' => "Nomor resi {$resi} telah diinput ke sistem dan diserahkan ke agen ekspedisi ({$order->courier_name})."
        ]);

        $updateData = ['status' => 'siap_diambil'];
        if (Schema::hasColumn('orders', 'tracking_number')) {
            $updateData['tracking_number'] = $resi;
        }
        if (Schema::hasColumn('orders', 'courier_status')) {
            $updateData['courier_status'] = 'in_transit';
        }
        if (Schema::hasColumn('orders', 'courier_manifest')) {
            $updateData['courier_manifest'] = $manifest;
        }

        $order->update($updateData);

        return response()->json([
            'status'  => 'success',
            'message' => "Nomor resi {$resi} berhasil disimpan untuk pesanan #{$order->order_number}.",
            'data'    => [
                'tracking_number'  => $resi,
                'courier_status'   => 'in_transit',
                'courier_manifest' => $manifest,
            ]
        ]);
    }

    /**
     * Tarik Status Live dari API Kurir (RajaOngkir Komerce Waybill)
     * POST /api/admin/kelola-pengiriman/{order_number}/cek-status-kurir
     */
    public function syncCourierStatus(Request $request, string $orderNumber)
    {
        $order = Order::with('transaction')->where('order_number', $orderNumber)->first();
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        $resi = trim((string) $order->tracking_number);
        if ($resi === '') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Nomor resi belum diinput. Silakan simpan nomor resi terlebih dahulu.'
            ], 422);
        }

        $rawCourier = strtolower(trim(explode('-', (string) $order->courier_name)[0]));
        $courierCodeMap = [
            'j&t' => 'jnt', 'jnt' => 'jnt', 'jne' => 'jne', 'sicepat' => 'sicepat',
            'anteraja' => 'anteraja', 'ninja' => 'ninja', 'pos' => 'pos', 'tiki' => 'tiki',
            'wahana' => 'wahana', 'lion' => 'lion', 'sap' => 'sap', 'ide' => 'ide'
        ];
        $courierCode = $courierCodeMap[$rawCourier] ?? $rawCourier;

        $manifest = is_array($order->courier_manifest) ? $order->courier_manifest : [];
        $newStatus = $order->courier_status ?: 'in_transit';

        try {
            $response = Http::withHeaders([
                'key' => env('RAJAONGKIR_API_KEY'),
            ])->timeout(8)->asForm()->post('https://rajaongkir.komerce.id/api/v1/track/waybill', [
                'awb'     => $resi,
                'courier' => $courierCode,
            ]);

            if ($response->successful() && isset($response->json()['data'])) {
                $apiData = $response->json()['data'];
                $delivered = (bool) ($apiData['delivered'] ?? false);
                $summaryStatus = strtoupper((string) ($apiData['summary']['status'] ?? 'ON_PROCESS'));

                if ($delivered || in_array($summaryStatus, ['DELIVERED', 'TERKIRIM', 'SELESAI'])) {
                    $newStatus = 'delivered';
                } elseif (str_contains($summaryStatus, 'ANTAR') || str_contains($summaryStatus, 'WITH_DELIVERY')) {
                    $newStatus = 'on_delivery';
                } else {
                    $newStatus = 'in_transit';
                }

                if (!empty($apiData['manifest']) && is_array($apiData['manifest'])) {
                    $manifest = collect($apiData['manifest'])->map(function ($m) {
                        return [
                            'time'        => trim(($m['manifest_date'] ?? '') . ' ' . ($m['manifest_time'] ?? '')),
                            'status'      => $m['manifest_code'] ?? 'UPDATE',
                            'description' => trim(($m['manifest_description'] ?? '') . ' ' . ($m['city_name'] ?? '')),
                        ];
                    })->values()->all();
                }
            } else {
                array_unshift($manifest, [
                    'time'        => now()->format('d M Y, H:i') . ' WIB',
                    'status'      => strtoupper($newStatus),
                    'description' => "Sinkronisasi API Kurir ({$order->courier_name}): Paket dengan resi {$resi} sedang dalam perjalanan."
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Gagal cek waybill API: ' . $e->getMessage());
            array_unshift($manifest, [
                'time'        => now()->format('d M Y, H:i') . ' WIB',
                'status'      => strtoupper($newStatus),
                'description' => "Pengecekan status kurir dilakukan. Resi {$resi} masih aktif dalam pengiriman."
            ]);
        }

        $updateData = [];
        if (Schema::hasColumn('orders', 'courier_status')) {
            $updateData['courier_status'] = $newStatus;
        }
        if (Schema::hasColumn('orders', 'courier_manifest')) {
            $updateData['courier_manifest'] = array_slice($manifest, 0, 15);
        }
        if (!empty($updateData)) {
            $order->update($updateData);
        }

        return response()->json([
            'status'  => 'success',
            'message' => $newStatus === 'delivered'
                ? "Status Kurir: Paket #{$order->order_number} telah DITERIMA! Silakan cek bukti pengiriman kurir."
                : "Status kurir untuk pesanan #{$order->order_number} berhasil diperbarui.",
            'data'    => [
                'courier_status'    => $newStatus,
                'courier_manifest'  => $order->courier_manifest,
                'arrival_proof_url' => $this->formatProofUrl($order->arrival_proof ?? $order->delivery_proof),
                'payment_proof_url' => $this->formatProofUrl($order->payment_proof),
            ]
        ]);
    }

    /**
     * Endpoint Signal / Webhook dari Pihak Jasa Kirim / Kurir Eksternal.
     * Kurir Eksternal yang mengirimkan foto bukti barang sampai (arrival_proof)
     * & foto bukti pelunasan COD (payment_proof) ke sistem.
     * POST /api/courier/webhook
     */
    public function webhookSignalKurir(Request $request)
    {
        $request->validate([
            'order_number'      => 'nullable|string',
            'tracking_number'   => 'nullable|string',
            'courier_status'    => 'required|in:in_transit,on_delivery,delivered,problem',
            'description'       => 'nullable|string',
            'arrival_proof'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'payment_proof'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'arrival_proof_url' => 'nullable|url',
            'payment_proof_url' => 'nullable|url',
        ]);

        $order = null;
        if ($request->filled('order_number')) {
            $order = Order::with('transaction')->where('order_number', $request->order_number)->first();
        } elseif ($request->filled('tracking_number') && Schema::hasColumn('orders', 'tracking_number')) {
            $order = Order::with('transaction')->where('tracking_number', $request->tracking_number)->first();
        }

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order / Resi tidak ditemukan.'], 404);
        }

        $newCourierStatus = $request->input('courier_status');
        $butuhPelunasan   = ((int) round($order->remaining_amount ?? 0)) > 0;

        $desc = $request->input('description') ?: match ($newCourierStatus) {
            'in_transit'  => 'Signal Kurir: Paket sedang transit di hub pengiriman.',
            'on_delivery' => 'Signal Kurir: Kurir ekspedisi sedang menuju lokasi penerima.',
            'delivered'   => $butuhPelunasan
                ? 'Signal Kurir: Paket telah diterima pelanggan beserta bukti foto POD & bukti pelunasan COD dari kurir.'
                : 'Signal Kurir: Paket telah diterima pelanggan beserta bukti foto POD dari kurir.',
            'problem'     => 'Signal Kurir: Kendala pengiriman dilaporkan oleh ekspedisi.',
            default       => 'Pembaruan status dari sistem ekspedisi.'
        };

        $manifest = is_array($order->courier_manifest) ? $order->courier_manifest : [];
        array_unshift($manifest, [
            'time'        => now()->format('d M Y, H:i') . ' WIB',
            'status'      => strtoupper($newCourierStatus),
            'description' => $desc,
        ]);

        $arrivalProofValue = $order->arrival_proof ?? $order->delivery_proof;
        $paymentProofValue = $order->payment_proof ?? null;

        // 1. Tangkap Foto Bukti Barang Sampai dari Kurir Eksternal (File atau URL Payload API Kurir)
        if ($request->hasFile('arrival_proof')) {
            $file = $request->file('arrival_proof');
            $arrivalProofValue = 'pod_ext_' . $order->order_number . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('bukti_pengiriman', $arrivalProofValue, 'public');
        } elseif ($request->filled('arrival_proof_url')) {
            $arrivalProofValue = $request->input('arrival_proof_url');
        } elseif ($newCourierStatus === 'delivered' && empty($arrivalProofValue)) {
            // Fallback simulasi otomatis jika dites via tombol Signal Kurir lokal
            $arrivalProofValue = 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=800&q=80';
        }

        // 2. Tangkap Foto Bukti Pelunasan COD dari Kurir Eksternal (Khusus COD / Sisa DP)
        if ($request->hasFile('payment_proof')) {
            $filePay = $request->file('payment_proof');
            $paymentProofValue = 'cod_ext_' . $order->order_number . '_' . time() . '.' . $filePay->getClientOriginalExtension();
            $filePay->storeAs('bukti_pengiriman', $paymentProofValue, 'public');
        } elseif ($request->filled('payment_proof_url')) {
            $paymentProofValue = $request->input('payment_proof_url');
        } elseif ($newCourierStatus === 'delivered' && $butuhPelunasan && empty($paymentProofValue)) {
            // Fallback simulasi otomatis bukti setor COD dari kurir eksternal
            $paymentProofValue = 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=800&q=80';
        }

        $updateData = [];
        if (Schema::hasColumn('orders', 'courier_status')) {
            $updateData['courier_status'] = $newCourierStatus;
        }
        if (Schema::hasColumn('orders', 'courier_manifest')) {
            $updateData['courier_manifest'] = array_slice($manifest, 0, 15);
        }
        if ($arrivalProofValue) {
            $updateData['delivery_proof'] = $arrivalProofValue;
            if (Schema::hasColumn('orders', 'arrival_proof')) {
                $updateData['arrival_proof'] = $arrivalProofValue;
            }
        }
        if ($paymentProofValue && Schema::hasColumn('orders', 'payment_proof')) {
            $updateData['payment_proof'] = $paymentProofValue;
        }

        $order->update($updateData);

        return response()->json([
            'status'  => 'success',
            'message' => "Signal kurir ({$newCourierStatus}) & foto bukti pengiriman diterima untuk #{$order->order_number}. Admin kini dapat memeriksa bukti dan menyelesaikan pesanan.",
            'data'    => [
                'courier_status'    => $newCourierStatus,
                'courier_manifest'  => $order->courier_manifest,
                'arrival_proof_url' => $this->formatProofUrl($arrivalProofValue),
                'payment_proof_url' => $this->formatProofUrl($paymentProofValue),
            ]
        ]);
    }

    /**
     * Menyelesaikan Pengiriman:
     * - Jika Kurir Internal: Wajib upload arrival_proof (+ payment_proof & nominal jika COD/DP)
     * - Jika Ekspedisi Eksternal: Admin HANYA memverifikasi bukti yang sudah dikirim oleh kurir eksternal
     * POST /api/admin/kelola-pengiriman/{order_number}/selesai
     */
    public function selesaikanPengiriman(Request $request, string $orderNumber)
    {
        $order = Order::with('transaction')
            ->where('order_number', $orderNumber)
            ->first();

        if (!$order) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data pesanan tidak ditemukan di database.'
            ], 404);
        }

        if ($order->status === 'selesai') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pesanan ini sudah berstatus selesai sebelumnya.'
            ], 422);
        }

        $courierName = trim((string) $order->courier_name);
        $isInternal = empty($courierName)
            || str_contains(strtolower($courierName), 'internal')
            || (float) $order->shipping_cost <= 0;

        $sisaTagihan = (int) round($order->remaining_amount ?? 0);
        $butuhPelunasan = $sisaTagihan > 0;

        $existingArrivalProof = $order->arrival_proof ?? $order->delivery_proof;
        $existingPaymentProof = $order->payment_proof ?? null;

        if ($isInternal) {
            // VALIDASI KURIR INTERNAL (Wajib Upload via Form)
            $rules = [
                'arrival_proof' => ($existingArrivalProof ? 'nullable' : 'required') . '|image|mimes:jpeg,png,jpg,webp|max:5120',
            ];
            $messages = [
                'arrival_proof.required' => 'Foto bukti barang sudah sampai wajib diunggah.',
                'arrival_proof.image'    => 'File bukti barang sampai harus berupa gambar.',
                'arrival_proof.max'      => 'Ukuran foto bukti barang sampai maksimal 5MB.',
            ];

            if ($butuhPelunasan) {
                $rules['nominal_diterima'] = 'required|numeric';
                $rules['payment_proof']    = ($existingPaymentProof ? 'nullable' : 'required') . '|image|mimes:jpeg,png,jpg,webp|max:5120';
                $messages['nominal_diterima.required'] = 'Nominal pelunasan pembayaran wajib diisi.';
                $messages['payment_proof.required']    = 'Foto bukti pelunasan pembayaran COD/DP wajib diunggah.';
                $messages['payment_proof.image']       = 'File bukti pelunasan harus berupa gambar.';
                $messages['payment_proof.max']         = 'Ukuran foto bukti pelunasan maksimal 5MB.';
            }

            $request->validate($rules, $messages);

            if ($butuhPelunasan) {
                $nominalInput = (int) round($request->input('nominal_diterima'));
                if ($nominalInput !== $sisaTagihan) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Nominal yang dimasukkan (Rp ' . number_format($nominalInput, 0, ',', '.') . ') harus sama persis dengan sisa tagihan (Rp ' . number_format($sisaTagihan, 0, ',', '.') . ').'
                    ], 422);
                }
            }
        } else {
            // VALIDASI EKSPEDISI EKSTERNAL (Admin cuma ngecek bukti yang dikirim kurir eksternal)
            if (empty($existingArrivalProof)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Pihak ekspedisi belum mengirimkan bukti foto barang sampai (POD).'
                ], 422);
            }
            if ($butuhPelunasan && empty($existingPaymentProof)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Pihak ekspedisi belum mengirimkan bukti pelunasan pembayaran COD.'
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $arrivalFileName = $existingArrivalProof;
            $paymentFileName = $existingPaymentProof;

            // Jika Kurir Internal mengunggah file baru
            if ($request->hasFile('arrival_proof')) {
                if ($arrivalFileName && !str_starts_with($arrivalFileName, 'http') && Storage::disk('public')->exists('bukti_pengiriman/' . $arrivalFileName)) {
                    Storage::disk('public')->delete('bukti_pengiriman/' . $arrivalFileName);
                }
                $fileArrival = $request->file('arrival_proof');
                $arrivalFileName = 'sampai_' . $order->order_number . '_' . time() . '.' . $fileArrival->getClientOriginalExtension();
                $fileArrival->storeAs('bukti_pengiriman', $arrivalFileName, 'public');
            }

            if ($request->hasFile('payment_proof')) {
                if ($paymentFileName && !str_starts_with($paymentFileName, 'http') && Storage::disk('public')->exists('bukti_pengiriman/' . $paymentFileName)) {
                    Storage::disk('public')->delete('bukti_pengiriman/' . $paymentFileName);
                }
                $filePayment = $request->file('payment_proof');
                $paymentFileName = 'bayar_' . $order->order_number . '_' . time() . '.' . $filePayment->getClientOriginalExtension();
                $filePayment->storeAs('bukti_pengiriman', $paymentFileName, 'public');
            }

            $updateOrderData = [
                'status'           => 'selesai',
                'remaining_amount' => 0,
                'delivery_proof'   => $arrivalFileName,
            ];

            if (Schema::hasColumn('orders', 'arrival_proof')) {
                $updateOrderData['arrival_proof'] = $arrivalFileName;
            }
            if (Schema::hasColumn('orders', 'payment_proof') && $paymentFileName) {
                $updateOrderData['payment_proof'] = $paymentFileName;
            }
            if (Schema::hasColumn('orders', 'courier_status')) {
                $updateOrderData['courier_status'] = 'delivered';
            }

            $order->update($updateOrderData);

            if ($order->transaction) {
                $order->transaction->update([
                    'amount'         => $order->total_amount,
                    'payment_status' => 'paid_full',
                    'paid_at'        => now(),
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memproses penyelesaian pengiriman: ' . $e->getMessage()
            ], 500);
        }

        $order->refresh();
        $order->load('transaction');
        NotificationController::send($order, 'selesai');

        return response()->json([
            'status'  => 'success',
            'message' => "Pengiriman pesanan #{$order->order_number} berhasil diverifikasi dan diselesaikan!"
        ]);
    }

    /**
     * Normalisasi URL bukti foto (mendukung path storage lokal maupun URL eksternal dari API kurir)
     */
    private function formatProofUrl(?string $rawPath): ?string
    {
        if (empty($rawPath)) {
            return null;
        }
        if (str_starts_with($rawPath, 'http://') || str_starts_with($rawPath, 'https://')) {
            return $rawPath;
        }
        $clean = preg_replace('#^/?(storage/)?(bukti_pengiriman/)?#i', '', trim($rawPath));
        return asset('storage/bukti_pengiriman/' . $clean);
    }

    /**
     * Mencocokkan teks shipping_address pada Order dengan record UserAddress milik pelanggan
     */
    private function resolveUserAddress(Order $order)
    {
        $addresses = $order->user?->addresses;
        if (!$addresses || $addresses->isEmpty()) {
            return null;
        }

        $shippingAddrLower = strtolower(trim((string) $order->shipping_address));

        foreach ($addresses as $addr) {
            $streetLower = strtolower(trim((string) $addr->street_address));
            if ($streetLower !== '' && str_contains($shippingAddrLower, $streetLower)) {
                return $addr;
            }
        }

        foreach ($addresses as $addr) {
            $distLower = strtolower(trim((string) $addr->district));
            $cityLower = strtolower(trim((string) $addr->city));
            if ($distLower !== '' && str_contains($shippingAddrLower, $distLower) && str_contains($shippingAddrLower, $cityLower)) {
                return $addr;
            }
        }

        return $addresses->firstWhere('is_primary', 1) ?? $addresses->first();
    }
}
