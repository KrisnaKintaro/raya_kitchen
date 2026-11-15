<?php

namespace App\Http\Controllers\Api\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\BotTemplate;
use App\Models\UserNotification;
use Database\Seeders\BotTemplateSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $notifs = UserNotification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

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

    public function markAsRead(Request $request)
    {
        $userId = Auth::id();
        if ($request->filled('id')) {
            UserNotification::where('id', $request->id)->where('user_id', $userId)->update(['is_read' => true]);
        } else {
            UserNotification::where('user_id', $userId)->update(['is_read' => true]);
        }
        return response()->json(['status' => 'success']);
    }

    /**
     * Mengambil pesan dari tabel bot_templates di database.
     * Jika state belum ada di DB, otomatis disinkronkan dari BotTemplateSeeder agar tidak pernah hardcode.
     */
    public static function getTemplateMessage(string $state, array $placeholders = []): string
    {
        $template = BotTemplate::where('session_state', $state)->where('is_active', true)->first();

        if (!$template || empty(trim($template->message_text))) {
            $defaults = collect(BotTemplateSeeder::getDefaults())->keyBy('session_state');
            $def = $defaults->get($state);
            if ($def) {
                $template = BotTemplate::updateOrCreate(
                    ['session_state' => $state],
                    [
                        'description'  => $def['description'],
                        'message_text' => $def['message_text'],
                        'is_active'    => true,
                    ]
                );
            }
        }

        $text = $template->message_text ?? '';
        foreach ($placeholders as $key => $value) {
            $text = str_replace('{' . $key . '}', (string) $value, $text);
        }
        return $text;
    }

    /**
     * FUNGSI SENTRAL TRIGGER NOTIFIKASI (WEB & WHATSAPP BOT)
     */
    public static function send($order, $type)
    {
        if (!$order) return;

        $title = '';
        $message = '';
        $waMessage = '';

        $linkNota       = url('/nota?order_number=' . $order->order_number);
        $linkBayar      = url('/pembayaran/' . $order->order_number);
        $sisaFormatted  = 'Rp ' . number_format($order->remaining_amount ?? 0, 0, ',', '.');
        $totalFormatted = 'Rp ' . number_format($order->total_amount ?? 0, 0, ',', '.');
        $dpFormatted    = 'Rp ' . number_format($order->dp_amount ?? 0, 0, ',', '.');

        $pMethod = strtolower($order->transaction->payment_method ?? 'transfer');
        $pStatus = strtolower($order->transaction->payment_status ?? 'unpaid');
        $isStillDp = ($order->remaining_amount > 0 && $pStatus === 'paid_dp');

        switch ($type) {
            case 'pembayaran_lunas':
                $title = 'Pembayaran Lunas Berhasil';
                $message = 'Pembayaran pesanan #' . $order->order_number . ' telah lunas diterima.';
                $waMessage = self::getTemplateMessage('notif_pembayaran_lunas', [
                    'nama_penerima' => $order->recipient_name,
                    'order_number'  => $order->order_number,
                    'total_pesanan' => $totalFormatted,
                    'link_nota'     => $linkNota
                ]);
                break;

            case 'pembayaran_dp':
                $title = 'Pembayaran DP 30% Berhasil';
                $message = 'DP 30% untuk pesanan #' . $order->order_number . ' berhasil diterima.';
                $waMessage = self::getTemplateMessage('notif_pembayaran_dp', [
                    'nama_penerima' => $order->recipient_name,
                    'order_number'  => $order->order_number,
                    'nominal_dp'    => $dpFormatted,
                    'sisa_tagihan'  => $sisaFormatted,
                    'link_nota'     => $linkNota
                ]);
                break;

            case 'pelunasan_berhasil':
                $title = 'Pelunasan Pesanan Berhasil';
                $message = 'Sisa tagihan pesanan #' . $order->order_number . ' telah lunas dibayar.';
                $waMessage = self::getTemplateMessage('notif_pelunasan_berhasil', [
                    'nama_penerima' => $order->recipient_name,
                    'order_number'  => $order->order_number,
                    'link_nota'     => $linkNota
                ]);
                break;

            case 'pembayaran_gagal':
                $title = 'Pembayaran Gagal';
                $message = 'Pembayaran QRIS untuk pesanan #' . $order->order_number . ' gagal atau kadaluarsa.';
                $waMessage = self::getTemplateMessage('notif_pembayaran_gagal', [
                    'nama_penerima'   => $order->recipient_name,
                    'order_number'    => $order->order_number,
                    'link_nota'       => $linkNota,
                    'link_pembayaran' => $linkBayar
                ]);
                break;

            case 'batal_otomatis':
                $title = 'Pesanan Dibatalkan';
                $message = 'Waktu pembayaran QRIS habis. Pesanan #' . $order->order_number . ' dibatalkan otomatis.';
                $waMessage = self::getTemplateMessage('notif_batal_otomatis', [
                    'nama_penerima' => $order->recipient_name,
                    'order_number'  => $order->order_number,
                    'link_nota'     => $linkNota
                ]);
                break;

            case 'diproses':
                $title = 'Pesanan Diproses';
                $message = 'Yay! Pesanan #' . $order->order_number . ' sedang dibuat oleh tim dapur kami.';
                $waMessage = self::getTemplateMessage('notif_diproses', [
                    'nama_penerima' => $order->recipient_name,
                    'order_number'  => $order->order_number,
                    'link_nota'     => $linkNota
                ]);
                break;

            case 'menunggu_pelunasan':
            case 'diantar':
            case 'siap_diambil':
            case 'siap_diantar':
            case 'siap_dikirim':
                if ($isStillDp) {
                    if ($pMethod === 'cod') {
                        $title = 'Pesanan Siap Dikirim - Segera Lunasi (COD)!';
                        $message = 'Pesanan #' . $order->order_number . ' sudah siap dikirim! Segera siapkan pelunasan COD sebesar ' . $sisaFormatted . ' (Klik untuk lihat Nota).';
                        $waMessage = self::getTemplateMessage('notif_menunggu_pelunasan_cod', [
                            'nama_penerima'   => $order->recipient_name,
                            'order_number'    => $order->order_number,
                            'sisa_tagihan'    => $sisaFormatted,
                            'link_pembayaran' => $linkBayar,
                            'link_nota'       => $linkNota
                        ]);
                    } else {
                        $title = 'Pesanan Siap Dikirim - Segera Lunasi Tagihan!';
                        $message = 'Pesanan #' . $order->order_number . ' sudah siap dikirim! Segera selesaikan pelunasan sisa DP sebesar ' . $sisaFormatted . ' (Klik untuk buka Nota & Bayar).';
                        $waMessage = self::getTemplateMessage('notif_menunggu_pelunasan', [
                            'nama_penerima'   => $order->recipient_name,
                            'order_number'    => $order->order_number,
                            'sisa_tagihan'    => $sisaFormatted,
                            'link_pembayaran' => $linkBayar,
                            'link_nota'       => $linkNota
                        ]);
                    }
                } else {
                    $title = 'Pesanan Siap Dikirim/Diambil';
                    $message = 'Pesanan #' . $order->order_number . ' sudah siap dikirim dan sudah lunas!';
                    $waMessage = self::getTemplateMessage('notif_diantar', [
                        'nama_penerima' => $order->recipient_name,
                        'order_number'  => $order->order_number,
                        'link_nota'     => $linkNota
                    ]);
                }
                break;

            case 'selesai':
                $title = 'Pesanan Selesai & Bukti Pengiriman';
                $message = 'Transaksi #' . $order->order_number . ' telah selesai! Klik untuk melihat nota dan foto bukti pengiriman kurir.';
                $waMessage = self::getTemplateMessage('notif_selesai', [
                    'nama_penerima' => $order->recipient_name,
                    'order_number'  => $order->order_number,
                    'link_nota'     => $linkNota
                ]);
                break;
        }

        // 1. Simpan Notifikasi Web
        if ($title !== '' && $order->user_id) {
            UserNotification::create([
                'user_id' => $order->user_id,
                'order_number' => $order->order_number,
                'title' => $title,
                'message' => $message
            ]);
        }

        // 2. Kirim Notifikasi WhatsApp via Fonnte
        if ($waMessage !== '' && !empty($order->recipient_whatsapp)) {
            try {
                $target = preg_replace('/[^0-9]/', '', $order->recipient_whatsapp);
                if (str_starts_with($target, '0')) {
                    $target = '62' . substr($target, 1);
                }

                Http::asForm()->withHeaders([
                    'Authorization' => env('FONNTE_TOKEN'),
                ])->post('https://api.fonnte.com/send', [
                    'target'  => $target,
                    'message' => $waMessage,
                    'typing'  => true,
                    'delay'   => '1',
                ]);
            } catch (\Exception $e) {
                Log::error('Gagal kirim WA Notifikasi (' . $type . '): ' . $e->getMessage());
            }
        }
    }
}
