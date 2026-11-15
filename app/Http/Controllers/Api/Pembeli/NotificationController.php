<?php

namespace App\Http\Controllers\Api\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\BotTemplate;
use App\Models\UserNotification;
use App\Models\WhatsappSession;
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
     * Hitung nominal dana yang harus direfund (DP 30% atau Lunas 100%)
     */
    public static function calculateRefundAmount($order): int
    {
        $totalAmt = (int) round($order->total_amount ?? 0);
        $dpAmt    = (int) round($order->dp_amount ?? 0);
        $remAmt   = (int) round($order->remaining_amount ?? 0);
        $trxAmt   = (int) round($order->transaction->amount ?? 0);
        $pStatus  = strtolower($order->transaction->payment_status ?? 'unpaid');

        $isFullRefund = ($pStatus === 'paid_full')
            || ($remAmt <= 0 && $totalAmt > 0)
            || ($trxAmt >= $totalAmt && $totalAmt > 0);

        return $isFullRefund
            ? $totalAmt
            : ($dpAmt > 0 ? $dpAmt : ($trxAmt > 0 ? $trxAmt : (int) round($totalAmt * 0.3)));
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
        $alasanBatal    = $order->cancellation_reason ?: 'Kendala operasional dapur.';

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
                $title = 'Pesanan Dibatalkan Otomatis';
                $message = 'Waktu pembayaran QRIS habis (24 jam). Pesanan #' . $order->order_number . ' dibatalkan otomatis.';
                $waMessage = self::getTemplateMessage('notif_batal_otomatis', [
                    'nama_penerima' => $order->recipient_name,
                    'order_number'  => $order->order_number,
                    'link_nota'     => $linkNota
                ]);
                break;

            case 'batal_admin_unpaid':
                $title = 'Pesanan Dibatalkan oleh Admin';
                $message = "Mohon maaf, pesanan #{$order->order_number} dibatalkan oleh Admin. Alasan: {$alasanBatal}";
                $waMessage = self::getTemplateMessage('notif_batal_admin_unpaid', [
                    'nama_penerima' => $order->recipient_name,
                    'order_number'  => $order->order_number,
                    'alasan_batal'  => $alasanBatal,
                    'link_nota'     => $linkNota,
                ]);
                break;

            case 'batal_admin_refund':
                $nominalRefund   = self::calculateRefundAmount($order);
                $refundFormatted = 'Rp ' . number_format($nominalRefund, 0, ',', '.');

                $title = 'Pesanan Dibatalkan Admin - Isi Rekening Refund!';
                $message = "Pesanan #{$order->order_number} dibatalkan Admin ({$alasanBatal}). Klik di sini untuk mengisi nomor rekening refund sebesar {$refundFormatted}.";
                $waMessage = self::getTemplateMessage('notif_batal_admin_refund', [
                    'nama_penerima'  => $order->recipient_name,
                    'order_number'   => $order->order_number,
                    'alasan_batal'   => $alasanBatal,
                    'nominal_refund' => $refundFormatted,
                    'link_nota'      => $linkNota,
                ]);

                // Arahkan sesi WhatsApp Bot pembeli agar siap menerima balasan nomor rekening langsung
                if (!empty($order->recipient_whatsapp)) {
                    $waNorm = preg_replace('/[^0-9]/', '', $order->recipient_whatsapp);
                    if (str_starts_with($waNorm, '0')) {
                        $waNorm = '62' . substr($waNorm, 1);
                    } elseif (!str_starts_with($waNorm, '62')) {
                        $waNorm = '62' . $waNorm;
                    }

                    $session = WhatsappSession::firstOrCreate(
                        ['whatsapp_number' => $waNorm],
                        [
                            'current_step'        => 'sapaan',
                            'session_data'        => [],
                            'last_interaction_at' => now(),
                        ]
                    );
                    $sd = is_array($session->session_data) ? $session->session_data : [];
                    $sd['refund_order_number'] = $order->order_number;

                    $session->update([
                        'current_step'        => 'input_rekening_refund_admin',
                        'session_data'        => $sd,
                        'last_interaction_at' => now(),
                    ]);
                }
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

            case 'refund_selesai':
                $nominalRefund   = self::calculateRefundAmount($order);
                $refundFormatted = 'Rp ' . number_format($nominalRefund, 0, ',', '.');
                $linkBuktiRefund = !empty($order->refund_proof)
                    ? (str_starts_with($order->refund_proof, 'http')
                        ? $order->refund_proof
                        : asset('storage/bukti_refund/' . preg_replace('#^/?(storage/)?(bukti_refund/)?#i', '', trim($order->refund_proof))))
                    : $linkNota;

                $title = 'Pengembalian Dana (Refund) Selesai';
                $message = 'Dana refund sebesar ' . $refundFormatted . ' untuk pesanan #' . $order->order_number . ' telah ditransfer oleh Admin. Klik untuk melihat bukti transfer di Nota.';
                $waMessage = self::getTemplateMessage('notif_refund_selesai', [
                    'nama_penerima'     => $order->recipient_name,
                    'order_number'      => $order->order_number,
                    'nominal_refund'    => $refundFormatted,
                    'rekening_refund'   => $order->refund_account_number ?? '-',
                    'link_bukti_refund' => $linkBuktiRefund,
                    'link_nota'         => $linkNota,
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