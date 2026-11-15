<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Pembeli\NotificationController;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BatalRefundController extends Controller
{
    /**
     * Mengambil daftar pesanan berstatus dibatalkan yang membutuhkan / sudah dilakukan refund
     * GET /api/admin/batal-refund
     */
    public function index(Request $request)
    {
        // Sinkronisasi otomatis: jika ada order dibatalkan yang punya nomor rekening refund
        // namun refund_status masih 'none', ubah menjadi 'pending' agar tidak terlewat oleh Admin.
        Order::where('status', 'dibatalkan')
            ->whereNotNull('refund_account_number')
            ->where('refund_account_number', '!=', '')
            ->where('refund_status', 'none')
            ->update(['refund_status' => 'pending']);

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
            ->where('status', 'dibatalkan')
            ->whereIn('refund_status', ['pending', 'refunded'])
            ->orderByRaw("CASE WHEN refund_status = 'pending' THEN 0 ELSE 1 END ASC")
            ->orderBy('updated_at', 'desc')
            ->get();

        $formatted = $orders->map(function (Order $order) {
            return $this->transformRefundOrder($order);
        })->values();

        $pendingCount  = $formatted->where('refund_status', 'pending')->count();
        $refundedCount = $formatted->where('refund_status', 'refunded')->count();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'orders'  => $formatted,
                'summary' => [
                    'pending_count'  => $pendingCount,
                    'refunded_count' => $refundedCount,
                    'total_count'    => $formatted->count(),
                ],
            ],
        ]);
    }

    /**
     * Endpoint ringan khusus mengambil jumlah antrean refund yang masih pending (untuk Badge Sidebar)
     * GET /api/admin/batal-refund/count
     */
    public function pendingCount()
    {
        $count = Order::where('status', 'dibatalkan')
            ->where(function ($q) {
                $q->where('refund_status', 'pending')
                  ->orWhere(function ($sq) {
                      $sq->whereNotNull('refund_account_number')
                         ->where('refund_account_number', '!=', '')
                         ->where('refund_status', 'none');
                  });
            })
            ->count();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'pending_count' => $count,
            ],
        ]);
    }

    /**
     * Memproses penyelesaian refund & unggah foto bukti transfer pengembalian dana
     * POST /api/admin/batal-refund/{order_number}/proses
     */
    public function prosesRefund(Request $request, string $orderNumber)
    {
        $request->validate([
            'refund_proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'refund_proof.required' => 'Foto bukti transfer pengembalian dana wajib diunggah.',
            'refund_proof.image'    => 'File bukti transfer harus berupa gambar.',
            'refund_proof.mimes'    => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'refund_proof.max'      => 'Ukuran gambar bukti transfer maksimal 2MB.',
        ]);

        $order = Order::with(['transaction', 'items.productVariant.product'])
            ->where('order_number', $orderNumber)
            ->where('status', 'dibatalkan')
            ->first();

        if (!$order) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data pesanan batal tidak ditemukan.',
            ], 404);
        }

        if ($order->refund_status === 'refunded') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pengembalian dana untuk pesanan ini sudah diselesaikan sebelumnya.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            if ($order->refund_proof && !str_starts_with($order->refund_proof, 'http')) {
                if (Storage::disk('public')->exists('bukti_refund/' . $order->refund_proof)) {
                    Storage::disk('public')->delete('bukti_refund/' . $order->refund_proof);
                }
            }

            $file = $request->file('refund_proof');
            $fileName = 'refund_' . $order->order_number . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('bukti_refund', $fileName, 'public');

            $order->update([
                'refund_proof'  => $fileName,
                'refund_status' => 'refunded',
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan bukti refund: ' . $e->getMessage(),
            ], 500);
        }

        $order->refresh();

        // Trigger notifikasi Web & WhatsApp ke pembeli bahwa dana refund telah ditransfer
        NotificationController::send($order, 'refund_selesai');

        $pendingCount = Order::where('status', 'dibatalkan')
            ->where('refund_status', 'pending')
            ->count();

        return response()->json([
            'status'  => 'success',
            'message' => "Bukti refund pesanan #{$order->order_number} berhasil disimpan dan diselesaikan.",
            'data'    => [
                'order'         => $this->transformRefundOrder($order),
                'pending_count' => $pendingCount,
            ],
        ]);
    }

    /**
     * Format struktur data Order untuk konsumsi UI Batal & Refund
     */
    private function transformRefundOrder(Order $order): array
    {
        $totalAmount     = (int) round($order->total_amount ?? 0);
        $dpAmount        = (int) round($order->dp_amount ?? 0);
        $remainingAmount = (int) round($order->remaining_amount ?? 0);
        $trxAmount       = (int) round($order->transaction->amount ?? 0);
        $pStatus         = strtolower($order->transaction->payment_status ?? 'unpaid');

        // Deteksi apakah pembayaran awal pelanggan adalah Lunas (100%) atau DP (30%)
        // meskipun saat dibatalkan status transaksi diubah menjadi 'failed'
        $isFullPayment = ($pStatus === 'paid_full')
            || ($remainingAmount <= 0 && $totalAmount > 0)
            || ($trxAmount >= $totalAmount && $totalAmount > 0);

        if ($isFullPayment) {
            $refundAmount = $totalAmount;
            $paymentTypeLabel = 'Lunas (100%)';
        } else {
            $refundAmount = $dpAmount > 0 ? $dpAmount : ($trxAmount > 0 ? $trxAmount : (int) round($totalAmount * 0.3));
            $paymentTypeLabel = 'DP 30%';
        }

        // Parsing string "Nama Bank - Nomor Rekening - Atas Nama"
        $rawAccount = trim((string) ($order->refund_account_number ?? ''));
        $parts = array_map('trim', explode('-', $rawAccount, 3));

        $bankName      = !empty($parts[0]) ? $parts[0] : 'Rekening / E-Wallet';
        $accountNumber = !empty($parts[1]) ? $parts[1] : ($rawAccount ?: '-');
        $accountHolder = !empty($parts[2]) ? $parts[2] : $order->recipient_name;

        $proofUrl = null;
        if (!empty($order->refund_proof)) {
            $proofUrl = str_starts_with($order->refund_proof, 'http')
                ? $order->refund_proof
                : asset('storage/bukti_refund/' . ltrim($order->refund_proof, '/'));
        }

        $waClean = preg_replace('/[^0-9]/', '', $order->recipient_whatsapp ?? '');
        if (str_starts_with($waClean, '0')) {
            $waClean = '62' . substr($waClean, 1);
        }

        return [
            'order_number'          => $order->order_number,
            'recipient_name'        => $order->recipient_name,
            'recipient_whatsapp'    => $order->recipient_whatsapp,
            'wa_chat_url'           => $waClean ? "https://wa.me/{$waClean}" : null,
            'order_source'          => $order->order_source ?? 'web',
            'cancelled_at'          => $order->updated_at ? $order->updated_at->locale('id')->isoFormat('D MMM YYYY, HH:mm') . ' WIB' : '-',
            'cancellation_reason'   => $order->cancellation_reason ?: 'Dibatalkan oleh pelanggan.',
            'refund_status'         => $order->refund_status ?: 'pending',
            'refund_amount'         => $refundAmount,
            'total_amount'          => $totalAmount,
            'dp_amount'             => $dpAmount,
            'payment_type_label'    => $paymentTypeLabel,
            'payment_method'        => strtoupper($order->transaction->payment_method ?? 'TRANSFER'),
            'refund_account_raw'    => $rawAccount,
            'bank_name'             => $bankName,
            'account_number'        => $accountNumber,
            'account_holder'        => $accountHolder,
            'refund_proof_url'      => $proofUrl,
            'items'                 => $order->items->map(function ($item) {
                $kategori = $item->productVariant?->product?->name ?? '';
                $varian   = $item->productVariant?->variant_name ?? 'Produk Roti';
                return [
                    'name'     => $kategori ? "{$kategori} - {$varian}" : $varian,
                    'qty'      => (int) $item->quantity,
                    'subtotal' => (int) round($item->subtotal),
                ];
            })->values()->all(),
        ];
    }
}
