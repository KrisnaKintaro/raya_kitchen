<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Pembeli\NotificationController;
use App\Models\Order;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class KelolaPengirimanController extends Controller
{
    private const COURIER_CODE_MAP = [
        'j&t'            => 'jnt',
        'jnt'            => 'jnt',
        'jne'            => 'jne',
        'sicepat'        => 'sicepat',
        'anteraja'       => 'anteraja',
        'ninja'          => 'ninja',
        'pos'            => 'pos',
        'tiki'           => 'tiki',
        'wahana'         => 'wahana',
        'lion'           => 'lion',
        'sap'            => 'sap',
        'ide'            => 'ide',
        'idexpress'      => 'ide',
        'id'             => 'ide',
        'ncs'            => 'ncs',
        'rex'            => 'rex',
        'rpx'            => 'rpx',
        'sentral'        => 'sentral',
        'star'           => 'star',
        'spx'            => 'spx',
        'shopee'         => 'spx',
        'shopeeexpress'  => 'spx',
    ];

    /**
     * Panduan format standar nomor resi asli per ekspedisi
     */
    private const COURIER_RESI_HINTS = [
        'JNE'      => '12-16 digit angka (cth: 15089274618293) atau awalan JNE / CM / TLJ / TKP',
        'JNT'      => 'Awalan JP / JD / JX / JB diikuti 10 digit angka (cth: JP1849204716)',
        'SICEPAT'  => '12 digit angka diawali 00 (cth: 004829174628)',
        'SPX'      => 'Awalan SPXID diikuti 10-12 karakter angka/huruf (cth: SPXID06456690742B)',
        'ANTERAJA' => '14 digit angka diawali 1000 atau 1100 (cth: 11004678609378)',
        'NINJA'    => 'Awalan NLIDAP / NVID diikuti 10-12 digit (cth: NLIDAP1583920471)',
        'TIKI'     => '12 digit angka diawali 03 (cth: 034829174625)',
        'POS'      => 'Awalan P diikuti 10-14 digit angka (cth: P261116849201)',
        'IDE'      => 'Awalan IDE / IDM diikuti 10-14 digit angka (cth: IDE104829174628)',
        'WAHANA'   => 'Awalan WAHANA / WHN diikuti 8-12 digit angka (cth: WHN2026849201)',
        'LION'     => 'Awalan LP diikuti 9-14 digit angka (cth: LP19482716492)',
        'SAP'      => 'Awalan SAP diikuti 8-12 digit angka (cth: SAP1948271649)',
    ];

    private function ensureShippingColumnsExist(): void
    {
        if (
            Schema::hasColumn('orders', 'tracking_number') &&
            Schema::hasColumn('orders', 'courier_status') &&
            Schema::hasColumn('orders', 'courier_manifest') &&
            Schema::hasColumn('orders', 'arrival_proof') &&
            Schema::hasColumn('orders', 'payment_proof')
        ) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number')->nullable()->after('shipping_service');
            }
            if (!Schema::hasColumn('orders', 'courier_status')) {
                $table->string('courier_status')->default('waiting_resi')->after('tracking_number');
            }
            if (!Schema::hasColumn('orders', 'courier_manifest')) {
                $table->json('courier_manifest')->nullable()->after('courier_status');
            }
            if (!Schema::hasColumn('orders', 'arrival_proof')) {
                $table->string('arrival_proof')->nullable()->after('delivery_proof');
            }
            if (!Schema::hasColumn('orders', 'payment_proof')) {
                $table->string('payment_proof')->nullable()->after('arrival_proof');
            }
        });
    }

    /**
     * Mengambil daftar pesanan yang siap dikirim (Kurir Internal & Ekspedisi)
     * GET /api/admin/kelola-pengiriman
     */
    public function index(Request $request)
    {
        $this->ensureShippingColumnsExist();

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
            $isInternal = $this->isInternalCourier($order);
            $courierName = trim((string) $order->courier_name);

            $matchedAddress = $this->resolveUserAddress($order);
            $lat = $matchedAddress?->latitude ? (float) $matchedAddress->latitude : null;
            $lng = $matchedAddress?->longitude ? (float) $matchedAddress->longitude : null;

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

            $expectedCourierCode = strtoupper($this->resolveCourierCode($order->courier_name));
            $resiFormatHint      = self::COURIER_RESI_HINTS[$expectedCourierCode]
                ?? "Awalan {$expectedCourierCode} diikuti minimal 8 digit angka";

            $formattedOrder = [
                'id'                  => $order->order_number,
                'order_db_id'         => $order->id,
                'type'                => $isInternal ? 'internal' : 'ekspedisi',
                'courier'             => $courierName ?: 'Kurir Internal Raya Kitchen',
                'courier_code'        => $expectedCourierCode,
                'resi_format_hint'    => $resiFormatHint,
                'shipping_service'    => $order->shipping_service ?: ($isInternal ? 'Kurir Toko' : 'Reguler'),
                'shipping_cost'       => (int) round($order->shipping_cost ?? 0),
                'tracking_number'     => $trackingNumber,
                'courier_status'      => $courierStatus,
                'courier_manifest'    => $manifest,
                'arrival_proof_url'   => $this->formatProofUrl($rawArrival),
                'payment_proof_url'   => $this->formatProofUrl($rawPayment),
                'recipient'           => $order->recipient_name,
                'recipient_whatsapp'  => $order->recipient_whatsapp,
                'wa_chat_url'         => $waClean ? "https://wa.me/{$waClean}" : null,
                'address'             => $order->shipping_address ?: 'Alamat belum diatur',
                'address_detail'      => [
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
                'delivery_date'       => $order->delivery_date ?? now()->toDateString(),
                'delivery_time'       => $order->delivery_time ?? '15:00:00',
                'payment_method'      => strtolower($order->transaction?->payment_method ?? 'transfer'),
                'payment_status'      => strtolower($order->transaction?->payment_status ?? 'unpaid'),
                'remaining_amount'    => (int) round($order->remaining_amount ?? 0),
                'dp_amount'           => (int) round($order->dp_amount ?? 0),
                'total_amount'        => (int) round($order->total_amount ?? 0),
                'items_count'         => (int) $order->items->sum('quantity'),
                'items'               => $order->items->map(function ($item) {
                    $kategori = $item->productVariant?->product?->name ?? '';
                    $varian   = $item->productVariant?->variant_name ?? 'Produk Roti';
                    return [
                        'order_item_id'      => (int) $item->id,
                        'product_variant_id' => (int) $item->product_variant_id,
                        'name'               => $kategori ? "{$kategori} - {$varian}" : $varian,
                        'qty'                => (int) $item->quantity,
                        'price'              => (int) round($item->price_at_order),
                        'subtotal'           => (int) round($item->subtotal),
                        'item_status'        => $item->item_status ?? 'selesai',
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
     * Fokus 2 Filter Utama:
     * 1. Cek apakah resi asli atau bohongan/asal ketik (Anti-Fake & Anti-Duplikat)
     * 2. Cek apakah resi sesuai dengan ekspedisi yang dipilih pembeli saat checkout (Anti-Silang Kurir)
     * POST /api/admin/kelola-pengiriman/{order_number}/resi
     */
    public function updateResi(Request $request, string $orderNumber)
    {
        $this->ensureShippingColumnsExist();

        $order = Order::with(['transaction', 'items.productVariant.product'])
            ->where('order_number', $orderNumber)
            ->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Data pesanan tidak ditemukan di database.'], 404);
        }

        if ($this->isInternalCourier($order)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pesanan ini menggunakan Kurir Internal Raya Kitchen sehingga tidak menggunakan nomor resi ekspedisi.'
            ], 422);
        }

        if (!empty($order->tracking_number) && in_array($order->courier_status, ['on_delivery', 'delivered'], true)) {
            return response()->json([
                'status'  => 'error',
                'message' => "Nomor resi ({$order->tracking_number}) sudah dikunci permanen karena paket sedang diantar ke alamat tujuan atau telah diterima."
            ], 422);
        }

        // Cek kesiapan dasar order (sudah selesai dimasak dapur & tidak punya tunggakan DP Transfer)
        $orderReadyError = $this->validateOrderReadyForShipping($order, true);
        if ($orderReadyError !== null) {
            return response()->json([
                'status'  => 'error',
                'message' => $orderReadyError
            ], 422);
        }

        $rawResi = strtoupper(trim((string) $request->input('tracking_number')));
        $request->merge(['tracking_number' => $rawResi]);

        // Validasi format karakter dasar & keunikan resi di database
        $request->validate([
            'tracking_number' => [
                'required',
                'string',
                'min:10',
                'max:25',
                'regex:/^[A-Z0-9\-]+$/',
                Rule::unique('orders', 'tracking_number')->ignore($order->id),
            ],
        ], [
            'tracking_number.required' => 'Nomor resi pengiriman wajib diisi.',
            'tracking_number.min'      => 'Nomor resi terlalu pendek (minimal 10 karakter/digit).',
            'tracking_number.max'      => 'Nomor resi terlalu panjang (maksimal 25 karakter/digit).',
            'tracking_number.regex'    => 'Format nomor resi hanya boleh berisi huruf kapital, angka, atau tanda hubung (-).',
            'tracking_number.unique'   => 'Ditolak! Nomor resi ini sudah digunakan pada pesanan lain di database.',
        ]);

        $courierCode = $this->resolveCourierCode($order->courier_name);

        // FILTER 1: Cek apakah resi bohongan / asal ketik
        $fakeResiError = $this->validateNotFakeResi($rawResi, $order->courier_name ?: $courierCode);
        if ($fakeResiError !== null) {
            return response()->json([
                'status'  => 'error',
                'message' => $fakeResiError
            ], 422);
        }

        // FILTER 2: Cek Anti-Silang Kurir (Harus sesuai dengan ekspedisi yang dipilih pembeli saat checkout)
        $courierMatchError = $this->validateResiMatchesSelectedCourier($rawResi, $courierCode, $order->courier_name);
        if ($courierMatchError !== null) {
            return response()->json([
                'status'  => 'error',
                'message' => $courierMatchError
            ], 422);
        }

        $manifest         = is_array($order->courier_manifest) ? $order->courier_manifest : [];
        $newCourierStatus = 'in_transit';

        array_unshift($manifest, [
            'time'        => now()->format('d M Y, H:i') . ' WIB',
            'status'      => 'IN_TRANSIT',
            'description' => "Nomor resi {$rawResi} terverifikasi sesuai ekspedisi {$order->courier_name}. Paket telah diserahkan ke pihak kurir untuk dikirim ke {$order->recipient_name}.",
        ]);

        $previousResi = $order->tracking_number;

        $order->update([
            'status'           => 'siap_diambil',
            'tracking_number'  => $rawResi,
            'courier_status'   => $newCourierStatus,
            'courier_manifest' => array_slice($manifest, 0, 15),
        ]);

        $order->refresh();
        $order->load(['transaction', 'items.productVariant.product']);

        // Kirim Notifikasi Otomatis ke Web & WhatsApp Pembeli
        if ($previousResi !== $rawResi) {
            NotificationController::send($order, 'resi_diinput');
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Nomor resi {$rawResi} ({$order->courier_name}) valid & berhasil dikunci! Notifikasi telah dikirim ke WA & Web pembeli.",
            'data'    => [
                'tracking_number'  => $rawResi,
                'courier_status'   => $newCourierStatus,
                'courier_manifest' => $order->courier_manifest,
            ]
        ]);
    }

    /**
     * Endpoint fallback sinkronisasi status kurir (tanpa ketergantungan API eksternal yang error)
     * POST /api/admin/kelola-pengiriman/{order_number}/cek-status-kurir
     */
    public function syncCourierStatus(Request $request, string $orderNumber)
    {
        $this->ensureShippingColumnsExist();

        $order = Order::with(['transaction', 'items.productVariant.product'])
            ->where('order_number', $orderNumber)
            ->first();

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

        $manifest  = is_array($order->courier_manifest) ? $order->courier_manifest : [];
        $newStatus = $order->courier_status ?: 'in_transit';

        return response()->json([
            'status'  => 'success',
            'message' => "Status resi {$resi} ({$order->courier_name}) saat ini: " . strtoupper($newStatus),
            'data'    => [
                'courier_status'    => $newStatus,
                'courier_manifest'  => $manifest,
                'arrival_proof_url' => $this->formatProofUrl($order->arrival_proof ?? $order->delivery_proof),
                'payment_proof_url' => $this->formatProofUrl($order->payment_proof),
            ]
        ]);
    }

    /**
     * Endpoint Update / Signal Status Kurir Eksternal (Menuju Lokasi / Barang Sampai POD)
     * POST /api/courier/webhook
     */
    public function webhookSignalKurir(Request $request)
    {
        $this->ensureShippingColumnsExist();

        $request->validate([
            'order_number'      => 'nullable|string',
            'tracking_number'   => 'nullable|string',
            'courier_status'    => 'required|in:in_transit,on_delivery,delivered,problem',
            'description'       => 'nullable|string',
            'cod_amount_paid'   => 'nullable|numeric',
            'arrival_proof'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'payment_proof'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'arrival_proof_url' => 'nullable|url',
            'payment_proof_url' => 'nullable|url',
        ]);

        $order = null;
        if ($request->filled('order_number') && $request->filled('tracking_number')) {
            $order = Order::with(['transaction', 'items.productVariant.product'])
                ->where('order_number', $request->order_number)
                ->where('tracking_number', strtoupper(trim($request->tracking_number)))
                ->first();

            if (!$order) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Ditolak! Nomor Resi tidak cocok dengan Nomor Pesanan di database.'
                ], 422);
            }
        } elseif ($request->filled('order_number')) {
            $order = Order::with(['transaction', 'items.productVariant.product'])->where('order_number', $request->order_number)->first();
        } elseif ($request->filled('tracking_number')) {
            $order = Order::with(['transaction', 'items.productVariant.product'])->where('tracking_number', strtoupper(trim($request->tracking_number)))->first();
        }

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order atau Nomor Resi tidak ditemukan di database.'], 404);
        }

        if (empty($order->tracking_number)) {
            return response()->json([
                'status'  => 'error',
                'message' => "Pesanan #{$order->order_number} belum memiliki nomor resi pengiriman! Silakan input nomor resi terlebih dahulu."
            ], 422);
        }

        $courierCode = $this->resolveCourierCode($order->courier_name);
        $courierMatchError = $this->validateResiMatchesSelectedCourier($order->tracking_number, $courierCode, $order->courier_name);
        if ($courierMatchError !== null) {
            return response()->json(['status' => 'error', 'message' => $courierMatchError], 422);
        }

        $newCourierStatus = $request->input('courier_status');
        $sisaTagihan      = (int) round($order->remaining_amount ?? 0);
        $butuhPelunasan   = $sisaTagihan > 0;

        if ($newCourierStatus === 'delivered' && $butuhPelunasan && $request->filled('cod_amount_paid')) {
            $codPaid = (int) round($request->input('cod_amount_paid'));
            if ($codPaid !== $sisaTagihan) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Nominal COD dari kurir (Rp " . number_format($codPaid, 0, ',', '.') . ") tidak sesuai dengan sisa tagihan di database (Rp " . number_format($sisaTagihan, 0, ',', '.') . ")."
                ], 422);
            }
        }

        $desc = $request->input('description') ?: match ($newCourierStatus) {
            'in_transit'  => "Paket {$order->courier_name} (Resi: {$order->tracking_number}) sedang transit di hub pengiriman.",
            'on_delivery' => "Kurir {$order->courier_name} (Resi: {$order->tracking_number}) sedang membawa paket menuju alamat penerima: {$order->recipient_name} ({$order->shipping_address}).",
            'delivered'   => $butuhPelunasan
                ? "Paket (Resi: {$order->tracking_number}) telah diterima oleh {$order->recipient_name} beserta bukti foto POD & pelunasan COD Rp " . number_format($sisaTagihan, 0, ',', '.') . "."
                : "Paket (Resi: {$order->tracking_number}) telah diterima oleh {$order->recipient_name} beserta bukti foto POD dari kurir.",
            'problem'     => "Kendala pengiriman dilaporkan oleh ekspedisi {$order->courier_name} untuk resi {$order->tracking_number}.",
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

        if ($request->hasFile('arrival_proof')) {
            $file = $request->file('arrival_proof');
            $arrivalProofValue = 'pod_ext_' . $order->order_number . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('bukti_pengiriman', $arrivalProofValue, 'public');
        } elseif ($request->filled('arrival_proof_url')) {
            $arrivalProofValue = $request->input('arrival_proof_url');
        } elseif ($newCourierStatus === 'delivered' && empty($arrivalProofValue)) {
            $arrivalProofValue = 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=800&q=80';
        }

        if ($request->hasFile('payment_proof')) {
            $filePay = $request->file('payment_proof');
            $paymentProofValue = 'cod_ext_' . $order->order_number . '_' . time() . '.' . $filePay->getClientOriginalExtension();
            $filePay->storeAs('bukti_pengiriman', $paymentProofValue, 'public');
        } elseif ($request->filled('payment_proof_url')) {
            $paymentProofValue = $request->input('payment_proof_url');
        } elseif ($newCourierStatus === 'delivered' && $butuhPelunasan && empty($paymentProofValue)) {
            $paymentProofValue = 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=800&q=80';
        }

        $updateData = [
            'courier_status'   => $newCourierStatus,
            'courier_manifest' => array_slice($manifest, 0, 15),
        ];

        if ($arrivalProofValue) {
            $updateData['delivery_proof'] = $arrivalProofValue;
            $updateData['arrival_proof']  = $arrivalProofValue;
        }

        if ($paymentProofValue) {
            $updateData['payment_proof'] = $paymentProofValue;
        }

        $order->update($updateData);
        $order->refresh();

        NotificationController::send($order, 'kurir_update', [
            'courier_status' => $newCourierStatus,
            'description'    => $desc,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Status pengiriman resi {$order->tracking_number} diperbarui ke " . strtoupper($newCourierStatus) . " & notifikasi dikirim ke pembeli!",
            'data'    => [
                'courier_status'    => $newCourierStatus,
                'courier_manifest'  => $order->courier_manifest,
                'arrival_proof_url' => $this->formatProofUrl($arrivalProofValue),
                'payment_proof_url' => $this->formatProofUrl($paymentProofValue),
            ]
        ]);
    }

    /**
     * Menyelesaikan Pengiriman
     * POST /api/admin/kelola-pengiriman/{order_number}/selesai
     */
    public function selesaikanPengiriman(Request $request, string $orderNumber)
    {
        $this->ensureShippingColumnsExist();

        $order = Order::with(['transaction', 'items.productVariant.product'])
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

        $isInternal = $this->isInternalCourier($order);

        $orderReadyError = $this->validateOrderReadyForShipping($order, !$isInternal);
        if ($orderReadyError !== null) {
            return response()->json([
                'status'  => 'error',
                'message' => $orderReadyError
            ], 422);
        }

        $sisaTagihan          = (int) round($order->remaining_amount ?? 0);
        $butuhPelunasan       = $sisaTagihan > 0;
        $existingArrivalProof = $order->arrival_proof ?? $order->delivery_proof;
        $existingPaymentProof = $order->payment_proof ?? null;

        if ($isInternal) {
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
                        'message' => 'Nominal yang dimasukkan (Rp ' . number_format($nominalInput, 0, ',', '.') . ') harus sama persis dengan sisa tagihan di database (Rp ' . number_format($sisaTagihan, 0, ',', '.') . ').'
                    ], 422);
                }
            }
        } else {
            if (empty(trim((string) $order->tracking_number))) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Nomor resi pengiriman belum diinput untuk pesanan ekspedisi ini.'
                ], 422);
            }

            $courierCode = $this->resolveCourierCode($order->courier_name);
            $courierMatchError = $this->validateResiMatchesSelectedCourier($order->tracking_number, $courierCode, $order->courier_name);
            if ($courierMatchError !== null) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $courierMatchError
                ], 422);
            }

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
                'arrival_proof'    => $arrivalFileName,
                'courier_status'   => 'delivered',
            ];

            if ($paymentFileName) {
                $updateOrderData['payment_proof'] = $paymentFileName;
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
        $order->load(['transaction', 'items.productVariant.product']);

        NotificationController::send($order, 'selesai');

        return response()->json([
            'status'  => 'success',
            'message' => "Pengiriman pesanan #{$order->order_number} berhasil diverifikasi dan diselesaikan!"
        ]);
    }

    // =========================================================================
    // HELPER: FILTER 1 (CEK RESI BOHONGAN) & FILTER 2 (CEK ANTI-SILANG KURIR)
    // =========================================================================

    private function isInternalCourier(Order $order): bool
    {
        $courierName = trim((string) $order->courier_name);
        return empty($courierName)
            || str_contains(strtolower($courierName), 'internal')
            || (float) $order->shipping_cost <= 0;
    }

    private function resolveCourierCode(?string $courierName): string
    {
        $rawCourier = strtolower(trim(explode('-', (string) $courierName)[0]));
        $rawCourier = str_replace(' ', '', $rawCourier);
        return self::COURIER_CODE_MAP[$rawCourier] ?? ($rawCourier ?: 'jne');
    }

    private function validateOrderReadyForShipping(Order $order, bool $isExternalCourier): ?string
    {
        if ($order->status === 'dibatalkan') {
            return "Pesanan #{$order->order_number} sudah dibatalkan sehingga tidak dapat diproses pengirimannya.";
        }

        if (!$order->transaction) {
            return "Data transaksi pembayaran untuk pesanan #{$order->order_number} tidak ditemukan.";
        }

        $pStatus = strtolower($order->transaction->payment_status ?? 'unpaid');
        $pMethod = strtolower($order->transaction->payment_method ?? 'transfer');

        if (!in_array($pStatus, ['paid_dp', 'paid_full'])) {
            return "Pesanan #{$order->order_number} belum dibayar (Status: " . strtoupper($pStatus) . ").";
        }

        $belumSelesaiMasak = $order->items->filter(function ($item) {
            return isset($item->item_status) && $item->item_status !== 'selesai';
        })->count();

        if ($belumSelesaiMasak > 0) {
            return "Terdapat {$belumSelesaiMasak} item roti pada pesanan #{$order->order_number} yang belum selesai dimasak di Pantauan Dapur.";
        }

        $remainingAmount = (int) round($order->remaining_amount ?? 0);
        if ($isExternalCourier && $pMethod === 'transfer' && $remainingAmount > 0) {
            return "Pesanan ekspedisi #{$order->order_number} menggunakan metode Transfer DP dan masih memiliki sisa tagihan Rp "
                 . number_format($remainingAmount, 0, ',', '.')
                 . ". Pelanggan wajib melunasi sisa tagihan terlebih dahulu sebelum paket dikirim.";
        }

        return null;
    }

    /**
     * FILTER 1: Mengecek apakah nomor resi yang dimasukkan adalah resi bohongan / asal ketik.
     */
    private function validateNotFakeResi(string $resi, string $courierDisplay): ?string
    {
        $cleanResi = strtoupper(str_replace(['-', ' '], '', $resi));

        // 1. Wajib mengandung minimal 6 digit angka (resi ekspedisi tidak mungkin huruf semua)
        $digitsOnly = preg_replace('/[^0-9]/', '', $cleanResi);
        if (strlen($digitsOnly) < 6) {
            return "Ditolak (Resi Tidak Valid)! Nomor resi '{$resi}' terdeteksi palsu karena kurang mengandung digit angka standar resi {$courierDisplay}.";
        }

        // 2. Tolak karakter kembar beruntun >= 6 kali (contoh: 1111111111, 0000000000, JP9999999999)
        if (preg_match('/(.)\1{5,}/', $cleanResi)) {
            return "Ditolak (Resi Bohongan)! Nomor resi '{$resi}' mengandung karakter berulang yang tidak wajar.";
        }

        // 3. Tolak urutan angka naik/turun (contoh: 1234567890, 9876543210, JP1234567890)
        if (
            str_contains('01234567890123456789', $digitsOnly) ||
            str_contains('98765432109876543210', $digitsOnly) ||
            preg_match('/(012345|123456|234567|345678|456789|987654|876543|765432|654321)/', $digitsOnly)
        ) {
            return "Ditolak (Resi Bohongan)! Nomor resi '{$resi}' menggunakan angka berurutan yang tidak valid.";
        }

        // 4. Tolak pola berulang 2-3 karakter (contoh: 121212121212, 123123123123)
        if (preg_match('/^(.{2,3})\1{3,}$/', $digitsOnly)) {
            return "Ditolak (Resi Bohongan)! Nomor resi '{$resi}' memiliki pola perulangan angka yang tidak valid.";
        }

        // 5. Tolak kata-kata dummy / ketikan keyboard acak
        $fakeKeywords = ['TEST', 'COBA', 'DUMMY', 'PALSU', 'BOHONG', 'CONTOH', 'SAMPLE', 'ADMIN', 'QWERTY', 'ASDFG', 'ZXCVB', 'ABCDEF', 'RESI'];
        foreach ($fakeKeywords as $word) {
            if (str_contains($cleanResi, $word)) {
                return "Ditolak (Resi Bohongan)! Nomor resi '{$resi}' mengandung kata tidak valid ('{$word}').";
            }
        }

        // 6. Cek variasi angka (minimal harus ada 4 angka berbeda di dalam bagian numerik resi)
        $uniqueDigits = count(array_unique(str_split($digitsOnly)));
        if ($uniqueDigits < 4) {
            return "Ditolak (Resi Bohongan)! Kombinasi angka pada resi '{$resi}' terlalu monoton dan tidak sesuai standar nomor resi asli.";
        }

        return null;
    }

    /**
     * FILTER 2: Mengecek apakah nomor resi sesuai dengan ekspedisi yang dipilih pembeli saat checkout (Anti-Silang Kurir).
     */
    private function validateResiMatchesSelectedCourier(string $resi, string $expectedCourierCode, ?string $courierDisplayName): ?string
    {
        $cleanResi = strtoupper(str_replace(['-', ' '], '', $resi));
        $expected  = strtoupper($expectedCourierCode);
        $display   = $courierDisplayName ?: $expected;

        // 1. Deteksi apakah nomor resi memiliki awalan/format milik kurir LAIN
        $detectedOwner = $this->detectResiCourierOwner($cleanResi);
        if ($detectedOwner !== null && $detectedOwner !== $expected) {
            $hint = self::COURIER_RESI_HINTS[$expected] ?? "format standar {$expected}";
            return "Ditolak (Silang Kurir)! Pembeli memilih ekspedisi {$display} ({$expected}) saat checkout, tetapi resi '{$resi}' adalah format milik kurir {$detectedOwner}. "
                 . "Wajib masukkan resi {$expected} ({$hint}).";
        }

        // 2. Validasi struktur resmi per ekspedisi yang dipilih pembeli
        $courierSpecificRegex = [
            'JNE'      => '/^(JNE[0-9A-Z]{8,16}|(CM|TLJ|TKP)[0-9]{9,14}|(?!00|03|1000|1100)[0-9]{12,16})$/',
            'JNT'      => '/^(J[PDXZB][0-9]{10}|JNT[0-9]{8,14})$/',
            'SICEPAT'  => '/^(00[0-9]{10}|SICEPAT[0-9]{8,12})$/',
            'SPX'      => '/^(SPXID[0-9A-Z]{10,14}|ID[0-9]{12,15}[A-Z]?)$/',
            'ANTERAJA' => '/^(1[01]00[0-9]{10}|ANTERAJA[0-9]{8,12})$/',
            'NINJA'    => '/^(NLIDAP[0-9]{8,12}|NVID[0-9A-Z]{8,12}|NINJA[0-9]{8,12})$/',
            'TIKI'     => '/^(03[0-9]{10}|TIKI[0-9]{8,12})$/',
            'POS'      => '/^(P[0-9]{10,14}|POS[0-9]{8,12})$/',
            'IDE'      => '/^(ID[EM][0-9]{10,14})$/',
            'WAHANA'   => '/^(WAHANA[0-9A-Z]{6,12}|WHN[0-9]{8,12})$/',
            'LION'     => '/^(LP[0-9]{9,14}|LION[0-9]{8,12})$/',
            'SAP'      => '/^(SAP[0-9]{8,12})$/',
        ];

        $pattern = $courierSpecificRegex[$expected] ?? '/^' . preg_quote($expected, '/') . '[0-9A-Z]{8,14}$/';

        if (!preg_match($pattern, $cleanResi)) {
            $hint = self::COURIER_RESI_HINTS[$expected] ?? "awalan {$expected} diikuti minimal 8 digit angka";
            return "Ditolak! Nomor resi '{$resi}' tidak sesuai dengan format resmi ekspedisi {$display} ({$expected}). "
                 . "Format {$expected} yang benar: {$hint}.";
        }

        return null;
    }

    /**
     * Mengenali pemilik ekspedisi berdasarkan pola prefix/struktur nomor resi
     */
    private function detectResiCourierOwner(string $cleanResi): ?string
    {
        if (preg_match('/^(SPXID[0-9A-Z]+|ID[0-9]{12,15}[A-Z])$/', $cleanResi)) return 'SPX';
        if (preg_match('/^(J[PDXZB][0-9]{10}|JNT[0-9A-Z]+)$/', $cleanResi)) return 'JNT';
        if (preg_match('/^(00[0-9]{10,12}|SICEPAT[0-9A-Z]+)$/', $cleanResi)) return 'SICEPAT';
        if (preg_match('/^(1[01]00[0-9]{10}|ANTERAJA[0-9A-Z]+)$/', $cleanResi)) return 'ANTERAJA';
        if (preg_match('/^(NLIDAP[0-9A-Z]+|NVID[0-9A-Z]+|NINJA[0-9A-Z]+)$/', $cleanResi)) return 'NINJA';
        if (preg_match('/^(03[0-9]{10,12}|TIKI[0-9A-Z]+)$/', $cleanResi)) return 'TIKI';
        if (preg_match('/^(P[0-9]{10,14}|POS[0-9A-Z]+)$/', $cleanResi)) return 'POS';
        if (preg_match('/^(ID[EM][0-9]{10,16})$/', $cleanResi)) return 'IDE';
        if (preg_match('/^(WAHANA[0-9A-Z]+|WHN[0-9]+)$/', $cleanResi)) return 'WAHANA';
        if (preg_match('/^(LION[0-9A-Z]+|LP[0-9]{9,14})$/', $cleanResi)) return 'LION';
        if (preg_match('/^(SAP[0-9A-Z]{7,15})$/', $cleanResi)) return 'SAP';
        if (preg_match('/^(JNE[0-9A-Z]+|(CM|TLJ|TKP)[0-9]{9,14})$/', $cleanResi)) return 'JNE';

        return null;
    }

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