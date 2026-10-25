<?php
namespace App\Http\Controllers\Api\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // Endpoint: Ambil data notifikasi untuk Navbar
    public function index()
    {
        $userId = Auth::id();

        // Ambil data 24 jam terakhir
        $notifs = UserNotification::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->get();

        // Hitung total notifikasi belum dibaca secara keseluruhan
        $unreadCount = UserNotification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'notifications' => $notifs,
                'unread_count' => $unreadCount
            ]
        ]);
    }

    // Endpoint: Tandai sudah dibaca
    public function markAsRead(Request $request)
    {
        $userId = Auth::id() ?? 1;
        if ($request->has('id')) {
            UserNotification::where('id', $request->id)->where('user_id', $userId)->update(['is_read' => true]);
        } else {
            // Tandai semua dibaca
            UserNotification::where('user_id', $userId)->update(['is_read' => true]);
        }
        return response()->json(['status' => 'success']);
    }

    /**
     * FUNGSI SENTRAL TRIGGER NOTIFIKASI
     * Panggil fungsi ini dari controller lain: NotificationController::send($order, 'tipe_notif');
     */
    public static function send($order, $type)
    {
        $title = '';
        $message = '';
        $waMessage = '';

        switch ($type) {
            case 'batal_otomatis':
                $title = 'Pesanan Dibatalkan';
                $message = 'Waktu pembayaran QRIS habis. Pesanan #' . $order->order_number . ' dibatalkan otomatis.';
                $waMessage = "Halo {$order->recipient_name},\n\nMohon maaf, pesananmu dengan nomor *{$order->order_number}* telah dibatalkan otomatis oleh sistem karena melewati batas waktu pembayaran 60 menit.\n\nSilakan buat pesanan baru di website Raya Kitchen jika masih berminat ya cuy!";
                break;

            case 'menunggu_pelunasan':
                // Cek pengaman tambahan: Pastikan dia transfer dan statusnya baru bayar DP
                if ($order->transaction && $order->transaction->payment_method === 'transfer' && $order->transaction->payment_status === 'paid_dp') {
                    $title = 'Menunggu Pelunasan';
                    $message = 'Pesanan #' . $order->order_number . ' sudah siap! Segera lunasi sisa tagihan Rp ' . number_format($order->remaining_amount, 0, ',', '.');
                    $waMessage = "Halo {$order->recipient_name},\n\nKabar baik! Pesananmu dengan nomor *{$order->order_number}* sudah SIAP DIANTAR/DIAMBIL.\n\nKarena kamu menggunakan sistem DP, masih ada sisa tagihan sebesar *Rp " . number_format($order->remaining_amount, 0, ',', '.') . "*. Mohon segera lakukan pelunasan agar pesanan bisa langsung diserahkan ya!";
                }
                break;

            case 'diproses':
                $title = 'Pesanan Diproses';
                $message = 'Yay! Pesanan #' . $order->order_number . ' sedang dibuat oleh tim dapur kami.';
                $waMessage = "Halo {$order->recipient_name},\n\nPesananmu dengan nomor *{$order->order_number}* sedang kami PROSES di dapur.\n\nTunggu update selanjutnya ya!";
                break;

            case 'diantar':
                $title = 'Pesanan Diantar/Siap';
                $message = 'Pesanan #' . $order->order_number . ' sudah siap dan menunggumu!';
                $waMessage = "Halo {$order->recipient_name},\n\nPesananmu dengan nomor *{$order->order_number}* sudah SIAP dan sedang dalam perjalanan / menunggu diambil.\n\nSiap-siap buat nikmatin roti fresh dari oven!";
                break;

            case 'selesai':
                $title = 'Pesanan Selesai';
                $message = 'Transaksi #' . $order->order_number . ' selesai. Terima kasih sudah berbelanja!';
                $waMessage = "Halo {$order->recipient_name},\n\nTransaksi untuk pesanan *{$order->order_number}* telah SELESAI.\n\nTerima kasih banyak sudah belanja di Raya Kitchen. Ditunggu pesanan selanjutnya!";
                break;
        }

        // 1. Eksekusi Notif Web (Simpan ke DB)
        if ($title !== '') {
            UserNotification::create([
                'user_id' => $order->user_id,
                'order_number' => $order->order_number,
                'title' => $title,
                'message' => $message
            ]);
        }

        // 2. Eksekusi Notif WhatsApp Bot (Persiapan)
        if ($waMessage !== '') {
            // Nanti tinggal buka comment di bawah pas sistem bot WA udah siap
            /*
            Http::post('URL_API_BOT_WA_LU', [
                'target' => $order->recipient_whatsapp,
                'message' => $waMessage
            ]);
            */
        }
    }
}
