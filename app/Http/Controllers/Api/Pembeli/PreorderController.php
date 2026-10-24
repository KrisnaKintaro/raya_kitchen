<?php

namespace App\Http\Controllers\Api\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PreorderController extends Controller
{
    // Settingan Kuota Harian
    private $maxVariantsPerDay = 5;
    private $maxQtyPerDay = 130;

    public function getHeatmap(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $userId = Auth::id() ?? 1;

        // 1. Ambil isi keranjang user yang lagi mau checkout
        $cartItems = Cart::where('user_id', $userId)->pluck('product_variant_id')->toArray();
        $cartQty = Cart::where('user_id', $userId)->sum('quantity');

        // Kalau keranjang kosong, cegah error
        if (empty($cartItems)) {
            return response()->json(['status' => 'error', 'message' => 'Keranjang kosong cuy!']);
        }

        // 2. Ambil data orderan orang lain di bulan tersebut (Yang nggak dibatalkan)
        $orders = Order::with('items')
            ->whereMonth('delivery_date', $bulan)
            ->whereYear('delivery_date', $tahun)
            ->whereNotIn('status', ['dibatalkan', 'pending']) // Asumsi pending = belum bayar DP. Kalo udah diverifikasi baru masuk kuota.
            ->get();

        // 3. Rekap total Qty & Varian per tanggal
        $rekapHarian = [];
        foreach ($orders as $order) {
            $tgl = $order->delivery_date;

            if (!isset($rekapHarian[$tgl])) {
                $rekapHarian[$tgl] = ['total_qty' => 0, 'variants' => []];
            }

            foreach ($order->items as $item) {
                $rekapHarian[$tgl]['total_qty'] += $item->quantity;
                // Masukin ID varian ke array kalau belum ada (biar dapet varian unik)
                if (!in_array($item->product_variant_id, $rekapHarian[$tgl]['variants'])) {
                    $rekapHarian[$tgl]['variants'][] = $item->product_variant_id;
                }
            }
        }

        // 4. Generate Heatmap buat kalender Flatpickr
        $heatmap = [];
        $daysInMonth = Carbon::create($tahun, $bulan)->daysInMonth;
        $today = Carbon::today()->format('Y-m-d');

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dateStr = sprintf("%04d-%02d-%02d", $tahun, $bulan, $i);

            // Kalau tanggal udah lewat, langsung disable
            if ($dateStr < $today) {
                $heatmap[$dateStr] = 'past';
                continue;
            }

            $rekap = $rekapHarian[$dateStr] ?? ['total_qty' => 0, 'variants' => []];

            $isFull = false;

            // Cek Syarat 1: Kapasitas Qty (Total order + isi keranjang)
            if (($rekap['total_qty'] + $cartQty) > $this->maxQtyPerDay) {
                $isFull = true;
            }

            // Cek Syarat 2: Limit Varian
            // Gabung varian pesanan hari itu + varian di keranjang user
            $mergedVariants = array_unique(array_merge($rekap['variants'], $cartItems));
            if (count($mergedVariants) > $this->maxVariantsPerDay) {
                $isFull = true;
            }

            // Tentukan Class Warna
            if ($isFull) {
                $heatmap[$dateStr] = 'full'; // Nanti di frontend diwarnain Merah (Disable)
            } elseif ($rekap['total_qty'] > ($this->maxQtyPerDay * 0.6)) {
                $heatmap[$dateStr] = 'med';  // Kuning (Udah lumayan rame)
            } else {
                $heatmap[$dateStr] = 'low';  // Hijau (Masih kosong/santai)
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $heatmap
        ]);
    }

    public function getAvailableTimes(Request $request)
    {
        // Karena batasan adalah "Harian", jam ini murni buat preferensi
        // user pengen pesanannya dikirim/diambil jam berapa.
        // Kita kasih standar jam operasional aja: 08:00 sampe 17:00

        $tanggal = Carbon::parse($request->tanggal);
        $jamOperasional = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];

        $availableTimes = [];
        $now = Carbon::now();

        foreach ($jamOperasional as $jam) {
            $jamLengkap = Carbon::parse($tanggal->format('Y-m-d') . ' ' . $jam);

            // Kalau user milih hari ini, jam yang udah kelewat diilangin
            if ($tanggal->isToday() && $now->greaterThanOrEqualTo($jamLengkap)) {
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
