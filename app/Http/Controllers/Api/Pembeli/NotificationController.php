<?php

namespace App\Http\Controllers\Api\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\BotTemplate;
use App\Models\UserNotification;
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
     * Helper mengambil template pesan dari tabel bot_templates
     */
    private static function getTemplate(string $state, array $placeholders = []): ?string
    {
        $template = BotTemplate::where('session_state', $state)->where('is_active', true)->first();
        if (!$template || empty(trim($template->message_text))) {
            return null;
        }

        $text = $template->message_text;
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

        switch ($type) {
            case 'pembayaran_lunas':
                $title = 'Pembayaran Lunas Berhasil';
                $message = 'Pembayaran pesanan #' . $order->order_number . ' telah lunas diterima.';
                $waMessage = self::getTemplate('notif_pembayaran_lunas', [
                    'nama_penerima' => $order->recipient_name,
                    'order_number'  => $order->order_number,
                    'total_pesanan' => $totalFormatted,
                    'link_nota'     => $linkNota
                ]) ?? "✅ *Pembayaran Berhasil (LUNAS)*\n\nHalo Kak *{$order->recipient_name}*,\nPembayaran untuk pesanan *#{$order->order_number}* sebesar *{$totalFormatted}* telah berhasil kami terima!\n\nPesananmu kini masuk antrean dapur kami.\n\n🧾 *Lihat Nota Digital:*\n{$linkNota}";
                break;

            case 'pembayaran_dp':
                $title = 'Pembayaran DP 30% Berhasil';
                $message = 'DP 30% untuk pesanan #' . $order->order_number . ' berhasil diterima.';
                $waMessage = self::getTemplate('notif_pembayaran_dp', [
                    'nama_penerima' => $order->recipient_name,
                    'order_number'  => $order->order_number,
                    'nominal_dp'    => $dpFormatted,
                    'sisa_tagihan'  => $sisaFormatted,
                    'link_nota'     => $linkNota
                ]) ?? "✅ *Pembayaran DP 30% Berhasil*\n\nHalo Kak *{$order->recipient_name}*,\nPembayaran DP sebesar *{$dpFormatted}* untuk pesanan *#{$order->order_number}* telah diterima!\nSisa tagihan sebesar *{$sisaFormatted}* dapat dilunasi maksimal saat pesanan sudah siap diantar/diambil.\n\n🧾 *Lihat Nota Digital & Status:*\n{$linkNota}";
                break;

            case 'pelunasan_berhasil':
                $title = 'Pelunasan Pesanan Berhasil';
                $message = 'Sisa tagihan pesanan #' . $order->order_number . ' telah lunas dibayar.';
                $statusInfo = ($order->status === 'selesai')
                    ? "Status pesananmu kini telah *SELESAI*."
                    : "Pesananmu kini sudah *LUNAS 100%* dan siap diserahkan.";
                $waMessage = self::getTemplate('notif_pelunasan_berhasil', [
                    'nama_penerima' => $order->recipient_name,
                    'order_number'  => $order->order_number,
                    'info_status'   => $statusInfo,
                    'link_nota'     => $linkNota
                ]) ?? "🎉 *Pelunasan Berhasil (LUNAS 100%)*\n\nHalo Kak *{$order->recipient_name}*,\nPelunasan tagihan untuk pesanan *#{$order->order_number}* telah berhasil kami terima! {$statusInfo}\n\n🧾 *Lihat Nota Digital:*\n{$linkNota}";
                break;

            case 'pembayaran_gagal':
                $title = 'Pembayaran Gagal';
                $message = 'Pembayaran QRIS untuk pesanan #' . $order->order_number . ' gagal atau kadaluarsa.';
                $waMessage = self::getTemplate('notif_pembayaran_gagal', [
                    'nama_penerima'   => $order->recipient_name,
                    'order_number'    => $order->order_number,
                    'link_nota'       => $linkNota,
                    'link_pembayaran' => $linkBayar
                ]) ?? "⚠️ *Pembayaran Belum Berhasil*\n\nHalo Kak *{$order->recipient_name}*,\nPembayaran QRIS untuk pesanan *#{$order->order_number}* gagal diproses atau melewati batas waktu.\n\n🧾 *Cek Detail Nota:*\n{$linkNota}\n👉 *Bayar Ulang QRIS:*\n{$linkBayar}";
                break;

            case 'batal_otomatis':
                $title = 'Pesanan Dibatalkan';
                $message = 'Waktu pembayaran QRIS habis. Pesanan #' . $order->order_number . ' dibatalkan otomatis.';
                $waMessage = self::getTemplate('notif_batal_otomatis', [
                    'nama_penerima' => $order->recipient_name,
                    'order_number'  => $order->order_number,
                    'link_nota'     => $linkNota
                ]) ?? "❌ *Pesanan Dibatalkan Otomatis*\n\nHalo Kak *{$order->recipient_name}*,\nMohon maaf, pesananmu dengan nomor *#{$order->order_number}* telah dibatalkan otomatis oleh sistem karena melewati batas waktu pembayaran 24 jam.\n\n🧾 *Detail Nota:*\n{$linkNota}";
                break;

            case 'menunggu_pelunasan':
                if ($order->transaction && $order->transaction->payment_method === 'transfer' && $order->transaction->payment_status === 'paid_dp') {
                    $title = 'Menunggu Pelunasan';
                    $message = 'Pesanan #' . $order->order_number . ' sudah siap! Segera lunasi sisa tagihan ' . $sisaFormatted;
                    $waMessage = self::getTemplate('notif_menunggu_pelunasan', [
                        'nama_penerima'   => $order->recipient_name,
                        'order_number'    => $order->order_number,
                        'sisa_tagihan'    => $sisaFormatted,
                        'link_pembayaran' => $linkBayar,
                        'link_nota'       => $linkNota
                    ]) ?? "⏳ *Menunggu Pelunasan Tagihan*\n\nHalo Kak *{$order->recipient_name}*,\nKabar baik! Pesananmu *#{$order->order_number}* sudah *SIAP DIANTAR/DIAMBIL*.\n\nKarena kamu menggunakan skema DP, masih ada sisa tagihan sebesar *{$sisaFormatted}*. Yuk segera lunasi lewat link di bawah agar pesanan bisa langsung diselesaikan:\n\n👉 *Link Pelunasan QRIS:*\n{$linkBayar}\n\n🧾 *Lihat Nota Digital:*\n{$linkNota}";
                }
                break;

            case 'diproses':
                $title = 'Pesanan Diproses';
                $message = 'Yay! Pesanan #' . $order->order_number . ' sedang dibuat oleh tim dapur kami.';
                $waMessage = self::getTemplate('notif_diproses', [
                    'nama_penerima' => $order->recipient_name,
                    'order_number'  => $order->order_number,
                    'link_nota'     => $linkNota
                ]) ?? "👨‍🍳 *Pesanan Sedang Diproses*\n\nHalo Kak *{$order->recipient_name}*,\nPesananmu dengan nomor *#{$order->order_number}* saat ini sedang kami *PROSES* di dapur Raya Kitchen.\n\n🧾 *Pantau Nota Digital:*\n{$linkNota}";
                break;

            case 'diantar':
            case 'siap_diambil':
            case 'siap_diantar':
                $title = 'Pesanan Siap Diantar/Diambil';
                $message = 'Pesanan #' . $order->order_number . ' sudah siap dan menunggumu!';
                $infoPelunasan = "Pesanan sudah lunas dan siap diserahkan.";
                if ($order->remaining_amount > 0 && $order->transaction && $order->transaction->payment_status === 'paid_dp') {
                    if ($order->transaction->payment_method === 'cod') {
                        $infoPelunasan = "💡 *Info COD:* Jangan lupa siapkan uang tunai sebesar *{$sisaFormatted}* untuk pelunasan saat pesanan tiba ya!";
                    } else {
                        $infoPelunasan = "⚠️ *Belum Lunas (Sisa {$sisaFormatted}):*\nSilakan lunasi sisa tagihanmu sebelum pesanan diselesaikan melalui link berikut:\n👉 {$linkBayar}";
                    }
                }
                $waMessage = self::getTemplate('notif_diantar', [
                    'nama_penerima'  => $order->recipient_name,
                    'order_number'   => $order->order_number,
                    'info_pelunasan' => $infoPelunasan,
                    'link_nota'      => $linkNota
                ]) ?? "🚚 *Pesanan Siap Diantar / Diambil*\n\nHalo Kak *{$order->recipient_name}*,\nPesananmu dengan nomor *#{$order->order_number}* sudah *SIAP* dan sedang dalam perjalanan / menunggu diambil!\n\n{$infoPelunasan}\n\n🧾 *Lihat Nota Digital:*\n{$linkNota}";
                break;

            case 'selesai':
                $title = 'Pesanan Selesai';
                $message = 'Transaksi #' . $order->order_number . ' selesai. Terima kasih sudah berbelanja!';
                $waMessage = self::getTemplate('notif_selesai', [
                    'nama_penerima' => $order->recipient_name,
                    'order_number'  => $order->order_number,
                    'link_nota'     => $linkNota
                ]) ?? "🥳 *Pesanan Selesai*\n\nHalo Kak *{$order->recipient_name}*,\nTransaksi untuk pesanan *#{$order->order_number}* telah *SELESAI*.\n\nTerima kasih banyak sudah berbelanja di Raya Kitchen. Ditunggu pesanan selanjutnya ya!\n\n🧾 *Nota Digital:*\n{$linkNota}";
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