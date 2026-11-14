<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Pembeli\NotificationController;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PantauanDapurController extends Controller
{
    /**
     * Mengambil seluruh daftar item produksi dapur yang aktif dari database
     * GET /api/admin/pantauan-dapur
     */
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data'   => [
                'items' => $this->getActiveKitchenItems()
            ]
        ]);
    }

    /**
     * Memindahkan status item di KDS (Batch maupun Individu),
     * menyimpan urutan prioritas, mengupdate status Order induk,
     * serta men-trigger Notifikasi WA & Web otomatis.
     * POST /api/admin/pantauan-dapur/move
     */
    public function moveItem(Request $request)
    {
        $request->validate([
            'order_item_id' => 'required|integer|exists:order_items,id',
            'new_status'    => 'required|in:pool,staging,diproses,selesai',
            'ordered_ids'   => 'nullable|array',
        ]);

        $item = OrderItem::with('order.transaction')->findOrFail($request->order_item_id);
        $order = $item->order;

        if (!$order) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data pesanan induk tidak ditemukan.'
            ], 404);
        }

        $oldStatus   = $item->item_status ?? 'pool';
        $newStatus   = $request->new_status;
        $tomorrowStr = Carbon::tomorrow()->toDateString();
        $isLocked    = ($order->delivery_date && $order->delivery_date <= $tomorrowStr);

        // Validasi Lock: Pesanan tenggat Hari Ini / Besok tidak boleh dikembalikan ke Pool
        if ($isLocked && $newStatus === 'pool') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses Ditolak! Item yang tenggatnya hari ini/besok wajib berada di Antrean Masak.'
            ], 422);
        }

        $toastMessage = 'Posisi antrean berhasil diperbarui.';
        $notifToTrigger = null;

        DB::beginTransaction();
        try {
            // 1. LOGIKA BATCHING (Pool <-> Staging: Pindahkan 1 nota rombongan sekaligus)
            if (($oldStatus === 'pool' && $newStatus === 'staging') || ($oldStatus === 'staging' && $newStatus === 'pool')) {
                OrderItem::where('order_id', $order->id)
                    ->where('item_status', $oldStatus)
                    ->update([
                        'item_status' => $newStatus,
                        'is_staged'   => ($newStatus === 'staging'),
                    ]);

                $toastMessage = $newStatus === 'staging'
                    ? "Semua item nota #{$order->order_number} ditarik ke Antrean Masak!"
                    : "Semua item nota #{$order->order_number} dikembalikan ke Pool.";
            }
            // 2. LOGIKA INDIVIDU (Antrean -> Diproses -> Selesai: 1 per 1 item)
            else {
                $item->update([
                    'item_status' => $newStatus,
                    'is_staged'   => ($newStatus !== 'pool'),
                ]);

                if ($oldStatus !== $newStatus) {
                    if ($newStatus === 'diproses') {
                        $toastMessage = "Item sedang dimasak (#{$order->order_number}).";
                    } elseif ($newStatus === 'selesai') {
                        $toastMessage = "1 item dari #{$order->order_number} selesai dimasak!";
                    } elseif ($newStatus === 'staging') {
                        $toastMessage = "Item dikembalikan ke Antrean Masak.";
                    }
                }
            }

            // 3. UPDATE URUTAN PRIORITAS (kitchen_priority) SESUAI POSISI KARTU DI KOLOM
            if ($request->filled('ordered_ids') && is_array($request->ordered_ids)) {
                foreach ($request->ordered_ids as $idx => $id) {
                    OrderItem::where('id', (int) $id)->update([
                        'kitchen_priority' => $idx + 1
                    ]);
                }
            }

            // 4. SINKRONISASI STATUS ORDER INDUK & PENENTUAN TRIGGER NOTIFIKASI
            $allOrderItems = OrderItem::where('order_id', $order->id)->get();
            $totalItems    = $allOrderItems->count();
            $selesaiCount  = $allOrderItems->where('item_status', 'selesai')->count();
            $diprosesCount = $allOrderItems->where('item_status', 'diproses')->count();

            $prevOrderStatus = $order->status;
            $newOrderStatus  = $prevOrderStatus;

            if ($totalItems > 0 && $selesaiCount === $totalItems) {
                // Seluruh item dalam nota ini sudah selesai dimasak -> Order Siap Diantar/Diambil
                $newOrderStatus = 'siap_diambil';
            } elseif ($diprosesCount > 0 || $selesaiCount > 0) {
                // Minimal ada 1 item yang sedang dimasak / selesai -> Order Sedang Diproses
                $newOrderStatus = 'diproses';
            } else {
                // Seluruh item masih di Pool / Staging -> Order Dalam Antrean (Pending)
                $newOrderStatus = 'pending';
            }

            if ($newOrderStatus !== $prevOrderStatus) {
                $order->update(['status' => $newOrderStatus]);

                // Trigger Notifikasi hanya saat status Order naik (Pending -> Diproses, atau -> Siap Diambil)
                if ($newOrderStatus === 'diproses' && $prevOrderStatus === 'pending') {
                    $notifToTrigger = 'diproses';
                    $toastMessage = "Nota #{$order->order_number} mulai diproses! Notifikasi dikirim ke pelanggan.";
                } elseif ($newOrderStatus === 'siap_diambil' && $prevOrderStatus !== 'siap_diambil') {
                    // Cek apakah pelanggan pakai Transfer DP & masih punya sisa tagihan
                    $pMethod = strtolower($order->transaction->payment_method ?? 'transfer');
                    $pStatus = strtolower($order->transaction->payment_status ?? 'unpaid');

                    if ($pMethod === 'transfer' && $pStatus === 'paid_dp' && $order->remaining_amount > 0) {
                        $notifToTrigger = 'menunggu_pelunasan';
                    } else {
                        $notifToTrigger = 'diantar';
                    }
                    $toastMessage = "Semua item #{$order->order_number} selesai! Notifikasi Siap Diantar dikirim ke pelanggan.";
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memperbarui status dapur: ' . $e->getMessage()
            ], 500);
        }

        // 5. EKSEKUSI PENGIRIMAN NOTIFIKASI WA & WEB SETELAH DB COMMIT
        if ($notifToTrigger) {
            $order->refresh();
            $order->load('transaction');
            NotificationController::send($order, $notifToTrigger);
        }

        return response()->json([
            'status'  => 'success',
            'message' => $toastMessage,
            'data'    => [
                'items' => $this->getActiveKitchenItems()
            ]
        ]);
    }

    /**
     * Helper: Mengambil semua item dapur aktif & meng-auto-stage pesanan H-0 / H+1
     */
    private function getActiveKitchenItems(): array
    {
        $tomorrowStr = Carbon::tomorrow()->toDateString();

        // Ambil semua item dari order yang pembayarannya sah (paid_dp / paid_full)
        // dan status ordernya belum 'selesai' (diserahkan ke pembeli) dan bukan 'dibatalkan'
        $items = OrderItem::with(['order.transaction', 'productVariant.product'])
            ->whereHas('order', function ($q) {
                $q->whereIn('status', ['pending', 'diproses', 'siap_diambil'])
                  ->whereHas('transaction', function ($tq) {
                      $tq->whereIn('payment_status', ['paid_dp', 'paid_full']);
                  });
            })
            ->get();

        // Auto-promote item di Pool yang tenggat kirimnya Hari Ini atau Besok ke Staging (Antrean Masak)
        foreach ($items as $item) {
            $delivDate = $item->order->delivery_date ?? null;
            $currentItemStatus = $item->item_status ?? 'pool';

            if ($delivDate && $delivDate <= $tomorrowStr && $currentItemStatus === 'pool') {
                $item->update([
                    'item_status' => 'staging',
                    'is_staged'   => true,
                ]);
                $item->item_status = 'staging';
                $item->is_staged   = true;
            }
        }

        // Urutkan berdasarkan kitchen_priority (jika > 0), lalu tanggal & jam pengiriman terdekat
        $sorted = $items->sort(function ($a, $b) {
            $prioA = (int) $a->kitchen_priority;
            $prioB = (int) $b->kitchen_priority;

            if ($prioA > 0 && $prioB > 0 && $prioA !== $prioB) {
                return $prioA <=> $prioB;
            }
            if ($prioA > 0 && $prioB === 0) return -1;
            if ($prioA === 0 && $prioB > 0) return 1;

            $dtA = ($a->order->delivery_date ?? '9999-12-31') . ' ' . ($a->order->delivery_time ?? '23:59:59');
            $dtB = ($b->order->delivery_date ?? '9999-12-31') . ' ' . ($b->order->delivery_time ?? '23:59:59');

            if ($dtA !== $dtB) {
                return $dtA <=> $dtB;
            }

            return $a->id <=> $b->id;
        })->values();

        return $sorted->map(function ($item) use ($tomorrowStr) {
            $order = $item->order;
            $kategori = $item->productVariant->product->name ?? '';
            $varian   = $item->productVariant->variant_name ?? 'Produk Roti';
            $namaProduk = $kategori ? "{$kategori} - {$varian}" : $varian;

            $delivDate = $order->delivery_date ?? Carbon::tomorrow()->toDateString();
            $delivTime = substr($order->delivery_time ?? '15:00:00', 0, 5);

            return [
                'order_item_id'    => (int) $item->id,
                'order_id'         => (int) $order->id,
                'order_number'     => $order->order_number,
                'customer_name'    => $order->recipient_name,
                'product_name'     => $namaProduk,
                'quantity'         => (int) $item->quantity,
                'deadline_date'    => $delivDate,
                'deadline_time'    => $delivTime,
                'item_status'      => $item->item_status ?? 'pool',
                'is_staged'        => (bool) $item->is_staged,
                'kitchen_priority' => (int) $item->kitchen_priority,
                'is_locked'        => ($delivDate <= $tomorrowStr),
                'order_status'     => $order->status,
            ];
        })->all();
    }
}
