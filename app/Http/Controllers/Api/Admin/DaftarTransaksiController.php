<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Pembeli\NotificationController;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DaftarTransaksiController extends Controller
{
    /**
     * Mengambil seluruh daftar transaksi dari database beserta ringkasan jumlah per status
     * GET /api/admin/daftar-transaksi
     */
    public function index(Request $request)
    {
        $orders = Order::with([
                'transaction',
                'items.productVariant' => function ($q) {
                    $q->withTrashed()->with([
                        'product' => function ($pq) {
                            $pq->withTrashed();
                        }
                    ]);
                },
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Auto-expire pesanan pending yang masih unpaid lewat dari 24 jam
        foreach ($orders as $order) {
            $expiredAt = $order->created_at ? $order->created_at->copy()->addMinutes(1440) : now();
            if (
                $order->status === 'pending' &&
                $order->transaction &&
                $order->transaction->payment_status === 'unpaid' &&
                now()->greaterThanOrEqualTo($expiredAt)
            ) {
                $order->update([
                    'status'              => 'dibatalkan',
                    'cancellation_reason' => 'Otomatis dibatalkan: Waktu pembayaran QRIS habis (24 jam).',
                ]);
                $order->transaction->update(['payment_status' => 'failed']);
                NotificationController::send($order, 'batal_otomatis');
            }
        }

        $formattedOrders = $orders->map(function (Order $order) {
            return $this->transformOrder($order);
        })->values();

        $counts = [
            'all'          => $formattedOrders->count(),
            'pending'      => $formattedOrders->where('status', 'pending')->count(),
            'diproses'     => $formattedOrders->where('status', 'diproses')->count(),
            'siap_diambil' => $formattedOrders->where('status', 'siap_diambil')->count(),
            'selesai'      => $formattedOrders->where('status', 'selesai')->count(),
            'dibatalkan'   => $formattedOrders->where('status', 'dibatalkan')->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data'   => [
                'orders' => $formattedOrders,
                'counts' => $counts,
            ],
        ]);
    }

    /**
     * Mengubah status pesanan, membatalkan pesanan, atau mengirim ulang pengingat pelunasan WA
     * POST /api/admin/daftar-transaksi/{order_number}/status
     */
    public function updateStatus(Request $request, string $orderNumber)
    {
        $request->validate([
            'action'                => 'required|in:diproses,siap_diambil,dibatalkan,remind_pelunasan',
            'cancellation_reason'   => 'nullable|string|max:500',
            'refund_account_number' => 'nullable|string|max:255',
        ]);

        $order = Order::with(['transaction', 'items.productVariant.product'])
            ->where('order_number', $orderNumber)
            ->first();

        if (!$order) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data transaksi tidak ditemukan.',
            ], 404);
        }

        $action  = $request->input('action');
        $pStatus = strtolower($order->transaction->payment_status ?? 'unpaid');
        $pMethod = strtolower($order->transaction->payment_method ?? 'transfer');

        // Aksi khusus: Kirim ulang pengingat pelunasan ke WhatsApp & Web Pembeli
        if ($action === 'remind_pelunasan') {
            if ($order->remaining_amount <= 0 || $pStatus !== 'paid_dp') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Pesanan ini tidak memiliki sisa tagihan DP.',
                ], 422);
            }

            $notifType = ($pMethod === 'transfer') ? 'menunggu_pelunasan' : 'diantar';
            NotificationController::send($order, $notifType);

            return response()->json([
                'status'  => 'success',
                'message' => "Pengingat pelunasan berhasil dikirim ke WhatsApp & Web pelanggan (#{$order->order_number}).",
                'data'    => [
                    'order' => $this->transformOrder($order),
                ],
            ]);
        }

        // Validasi jika ingin mengubah ke Diproses / Siap Diambil tapi belum dibayar sama sekali
        if (in_array($action, ['diproses', 'siap_diambil']) && in_array($pStatus, ['unpaid', 'failed'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pesanan belum dibayar oleh pelanggan sehingga belum dapat diproses.',
            ], 422);
        }

        $notifToTrigger = null;
        $toastMessage   = 'Status pesanan berhasil diperbarui.';

        DB::beginTransaction();
        try {
            if ($action === 'diproses') {
                $order->update(['status' => 'diproses']);
                OrderItem::where('order_id', $order->id)
                    ->whereIn('item_status', ['pool', 'staging'])
                    ->update([
                        'item_status' => 'diproses',
                        'is_staged'   => true,
                    ]);
                $notifToTrigger = 'diproses';
                $toastMessage   = "Pesanan #{$order->order_number} diubah menjadi Sedang Diproses.";
            } elseif ($action === 'siap_diambil') {
                $order->update(['status' => 'siap_diambil']);
                OrderItem::where('order_id', $order->id)
                    ->update([
                        'item_status' => 'selesai',
                        'is_staged'   => true,
                    ]);

                if ($pStatus === 'paid_dp' && $order->remaining_amount > 0) {
                    $notifToTrigger = ($pMethod === 'transfer') ? 'menunggu_pelunasan' : 'diantar';
                } else {
                    $notifToTrigger = 'diantar';
                }
                $toastMessage = "Pesanan #{$order->order_number} kini Siap Dikirim/Diambil & notifikasi dikirim.";
            } elseif ($action === 'dibatalkan') {
                $reason = trim((string) $request->input('cancellation_reason')) ?: 'Kendala operasional dapur.';
                $isPaid = in_array($pStatus, ['paid_dp', 'paid_full']) || ((int) round($order->dp_amount ?? 0) > 0 && $pStatus !== 'unpaid');

                $order->update([
                    'status'                => 'dibatalkan',
                    'cancellation_reason'   => $reason,
                    'refund_account_number' => $isPaid ? ($order->refund_account_number ?: null) : null,
                    'refund_status'         => $isPaid ? 'pending' : 'none',
                ]);

                if ($order->transaction) {
                    $order->transaction->update(['payment_status' => 'failed']);
                }

                $notifToTrigger = $isPaid ? 'batal_admin_refund' : 'batal_admin_unpaid';
                $toastMessage   = $isPaid
                    ? "Pesanan #{$order->order_number} dibatalkan. Notifikasi permintaan rekening refund telah dikirim ke pembeli."
                    : "Pesanan #{$order->order_number} berhasil dibatalkan & alasan pembatalan telah dikirim ke pembeli.";
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memperbarui status transaksi: ' . $e->getMessage(),
            ], 500);
        }

        $order->refresh();
        $order->load(['transaction', 'items.productVariant.product']);

        if ($notifToTrigger) {
            NotificationController::send($order, $notifToTrigger);
        }

        $pendingRefundCount = Order::where('status', 'dibatalkan')
            ->where('refund_status', 'pending')
            ->count();

        return response()->json([
            'status'  => 'success',
            'message' => $toastMessage,
            'data'    => [
                'order'                => $this->transformOrder($order),
                'pending_refund_count' => $pendingRefundCount,
            ],
        ]);
    }

    /**
     * Format data Order secara lengkap untuk kebutuhan UI Daftar Transaksi
     */
    private function transformOrder(Order $order): array
    {
        $rawStatus = strtolower(str_replace(' ', '_', $order->status ?? 'pending'));
        $normalizedStatus = in_array($rawStatus, ['siap_diambil', 'siap_diantar', 'siap_dikirim', 'dikirim', 'diantar'])
            ? 'siap_diambil'
            : $rawStatus;

        $pStatus = strtolower($order->transaction->payment_status ?? 'unpaid');
        $pMethod = strtolower($order->transaction->payment_method ?? 'transfer');

        $totalAmount     = (int) round($order->total_amount ?? 0);
        $dpAmount        = (int) round($order->dp_amount ?? 0);
        $remainingAmount = (int) round($order->remaining_amount ?? 0);
        $shippingCost    = (int) round($order->shipping_cost ?? 0);
        $subtotalItems   = (int) round($order->items->sum('subtotal'));

        // Hitung nominal yang sudah benar-benar dibayar oleh pelanggan
        if ($pStatus === 'paid_full' || $normalizedStatus === 'selesai') {
            $paidAmount = $totalAmount;
        } elseif ($pStatus === 'paid_dp') {
            $paidAmount = $remainingAmount > 0
                ? max(0, $totalAmount - $remainingAmount)
                : ($dpAmount > 0 ? $dpAmount : (int) round($totalAmount * 0.3));
        } else {
            $paidAmount = 0;
        }

        $isDpScheme = ($remainingAmount > 0) || ($pStatus === 'paid_dp');

        // Metadata Kurir & Jadwal/Estimasi Sampai Dinamis
        $courierName = trim((string) $order->courier_name);
        $isInternal  = empty($courierName)
            || str_contains(strtolower($courierName), 'internal')
            || $shippingCost <= 0;

        $baseDate = $order->created_at ? Carbon::parse($order->created_at) : now();

        if ($isInternal) {
            $delivDateStr = $order->delivery_date
                ? Carbon::parse($order->delivery_date)->locale('id')->isoFormat('D MMM YYYY')
                : $baseDate->copy()->addDay()->locale('id')->isoFormat('D MMM YYYY');
            $delivTimeStr  = $order->delivery_time ? substr($order->delivery_time, 0, 5) . ' WIB' : '15:00 WIB';
            $scheduleLabel = 'Jadwal Kirim / Ambil';
            $scheduleText  = "{$delivDateStr} | {$delivTimeStr}";
        } else {
            $scheduleLabel = 'Estimasi Tiba (Ekspedisi)';
            preg_match_all('/\d+/', (string) $order->shipping_service, $matches);
            $nums = !empty($matches[0]) ? array_map('intval', $matches[0]) : [];

            if (!empty($nums)) {
                $minD = max(1, min($nums));
                $maxD = max(1, max($nums));
                $minDateStr = $baseDate->copy()->addDays($minD)->locale('id')->isoFormat('D MMM YYYY');
                $maxDateStr = $baseDate->copy()->addDays($maxD)->locale('id')->isoFormat('D MMM YYYY');
                $etdLabel   = ($minD === $maxD) ? "{$maxD} Hari" : "{$minD}-{$maxD} Hari";
                $scheduleText = ($minD === $maxD)
                    ? "{$maxDateStr} (Est. {$etdLabel})"
                    : "{$minDateStr} - {$maxDateStr} (Est. {$etdLabel})";
            } elseif ($order->delivery_date) {
                $scheduleText = Carbon::parse($order->delivery_date)->locale('id')->isoFormat('D MMM YYYY');
            } else {
                $scheduleText = 'Mengikuti estimasi ekspedisi';
            }
        }

        $waClean = preg_replace('/[^0-9]/', '', $order->recipient_whatsapp ?? '');
        if (str_starts_with($waClean, '0')) {
            $waClean = '62' . substr($waClean, 1);
        }

        $rawArrival = $order->arrival_proof ?? $order->delivery_proof ?? null;
        $rawPayment = $order->payment_proof ?? null;
        $rawRefund  = $order->refund_proof ?? null;

        return [
            'order_number'          => $order->order_number,
            'status'                => $normalizedStatus,
            'raw_status'            => $rawStatus,
            'order_source'          => strtolower($order->order_source ?? 'web'),
            'created_at_formatted'  => $order->created_at ? $order->created_at->locale('id')->isoFormat('D MMM YYYY, HH:mm') . ' WIB' : '-',
            'recipient_name'        => $order->recipient_name,
            'recipient_whatsapp'    => $order->recipient_whatsapp,
            'wa_chat_url'           => $waClean ? "https://wa.me/{$waClean}" : null,
            'shipping_address'      => $order->shipping_address ?: 'Ambil di Toko / Belum diatur',
            'is_internal'           => $isInternal,
            'courier_name'          => $courierName ?: 'Kurir Internal Raya Kitchen',
            'shipping_service'      => $order->shipping_service ?: ($isInternal ? 'Kurir Toko' : 'Reguler'),
            'shipping_cost'         => $shippingCost,
            'tracking_number'       => $order->tracking_number,
            'courier_status'        => $order->courier_status,
            'schedule_label'        => $scheduleLabel,
            'schedule_text'         => $scheduleText,
            'payment_method'        => $pMethod,
            'payment_status'        => $pStatus,
            'payment_scheme_label'  => $isDpScheme ? 'DP 30%' : 'Lunas 100%',
            'subtotal_items'        => $subtotalItems,
            'dp_amount'             => $dpAmount,
            'paid_amount'           => $paidAmount,
            'remaining_amount'      => $remainingAmount,
            'total_amount'          => $totalAmount,
            'cancellation_reason'   => $order->cancellation_reason,
            'refund_status'         => $order->refund_status ?? 'none',
            'refund_account_number' => $order->refund_account_number,
            'arrival_proof_url'     => $this->formatStorageUrl($rawArrival, 'bukti_pengiriman'),
            'payment_proof_url'     => $this->formatStorageUrl($rawPayment, 'bukti_pengiriman'),
            'refund_proof_url'      => $this->formatStorageUrl($rawRefund, 'bukti_refund'),
            'nota_url'              => url('/nota?order_number=' . $order->order_number),
            'items_count'           => (int) $order->items->sum('quantity'),
            'items'                 => $order->items->map(function ($item) {
                $kategori = $item->productVariant?->product?->name ?? '';
                $varian   = $item->productVariant?->variant_name ?? 'Produk Roti';
                return [
                    'name'           => $kategori ? "{$kategori} - {$varian}" : $varian,
                    'quantity'       => (int) $item->quantity,
                    'price_at_order' => (int) round($item->price_at_order),
                    'subtotal'       => (int) round($item->subtotal),
                    'item_status'    => $item->item_status ?? 'pool',
                ];
            })->values()->all(),
        ];
    }

    private function formatStorageUrl(?string $rawPath, string $folder): ?string
    {
        if (empty($rawPath)) {
            return null;
        }
        if (str_starts_with($rawPath, 'http://') || str_starts_with($rawPath, 'https://')) {
            return $rawPath;
        }
        return asset("storage/{$folder}/" . ltrim($rawPath, '/'));
    }
}