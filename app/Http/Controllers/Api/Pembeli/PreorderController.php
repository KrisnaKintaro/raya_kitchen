<?php

namespace App\Http\Controllers\Api\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Cart;
use App\Models\StoreSetting;
use App\Models\StoreHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PreorderController extends Controller
{
    public function getHeatmap(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $userId = Auth::id() ?? 1;

        $setting = StoreSetting::first();
        $maxQtyPerDay = $setting?->max_qty_per_day ?? 60;

        $cartQty = Cart::where('user_id', $userId)->sum('quantity');

        if ($cartQty == 0) {
            return response()->json(['status' => 'error', 'message' => 'Keranjang lu kosong cuy!']);
        }

        $orders = Order::with('items')
            ->whereMonth('delivery_date', $bulan)
            ->whereYear('delivery_date', $tahun)
            ->whereNotIn('status', ['dibatalkan', 'pending'])
            ->get();

        $rekapHarian = [];
        foreach ($orders as $order) {
            $tgl = $order->delivery_date;
            if (!isset($rekapHarian[$tgl])) $rekapHarian[$tgl] = 0;

            foreach ($order->items as $item) {
                $rekapHarian[$tgl] += $item->quantity;
            }
        }

        $holidays = StoreHoliday::whereMonth('holiday_date', $bulan)
            ->whereYear('holiday_date', $tahun)
            ->pluck('holiday_date')->toArray();

        $heatmap = [];
        $daysInMonth = Carbon::create($tahun, $bulan)->daysInMonth;

        $besok = Carbon::tomorrow()->format('Y-m-d');

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dateStr = sprintf("%04d-%02d-%02d", $tahun, $bulan, $i);

            if ($dateStr < $besok) {
                $heatmap[$dateStr] = 'past';
                continue;
            }

            if (in_array($dateStr, $holidays)) {
                $heatmap[$dateStr] = 'full';
                continue;
            }

            $totalQtyHariIni = $rekapHarian[$dateStr] ?? 0;

            if (($totalQtyHariIni + $cartQty) > $maxQtyPerDay) {
                $heatmap[$dateStr] = 'full';
            } elseif ($totalQtyHariIni > ($maxQtyPerDay * 0.7)) {
                $heatmap[$dateStr] = 'med';
            } else {
                $heatmap[$dateStr] = 'low';
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $heatmap
        ]);
    }

    public function getAvailableTimes(Request $request)
    {
        $tanggal = Carbon::parse($request->tanggal);

        $setting = StoreSetting::first();
        $maxOrdersPerHour = $setting?->max_orders_per_hour ?? 5;

        $startHourStr = $setting?->operational_time_start ?? '08:00:00';
        $endHourStr = $setting?->operational_time_end ?? '17:00:00';

        $startHour = (int) Carbon::parse($startHourStr)->format('H');
        $endHour = (int) Carbon::parse($endHourStr)->format('H');

        $jamOperasional = [];
        for ($h = $startHour; $h <= $endHour; $h++) {
            $jamOperasional[] = sprintf("%02d:00", $h);
        }

        $nowPlus24 = Carbon::now()->addHours(24);

        $orders = Order::whereDate('delivery_date', $tanggal->format('Y-m-d'))
            ->whereNotIn('status', ['dibatalkan', 'pending'])
            ->get();

        $rekapJam = [];
        foreach ($orders as $order) {
            $jam = Carbon::parse($order->delivery_time)->format('H:i');
            if (!isset($rekapJam[$jam])) $rekapJam[$jam] = 0;
            $rekapJam[$jam]++;
        }

        $availableTimes = [];
        foreach ($jamOperasional as $jam) {
            $jamLengkap = Carbon::parse($tanggal->format('Y-m-d') . ' ' . $jam);

            if ($jamLengkap->lessThan($nowPlus24)) {
                continue;
            }

            $terisi = $rekapJam[$jam] ?? 0;
            if ($terisi >= $maxOrdersPerHour) {
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
