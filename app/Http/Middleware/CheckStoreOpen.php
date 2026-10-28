<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\StoreSetting;
use App\Models\StoreHoliday;
use Carbon\Carbon;

class CheckStoreOpen
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('toko-tutup')) {
            return $next($request);
        }

        $now = Carbon::now('Asia/Jakarta');
        $isClosed = false;
        $closeReason = '';

        // 1. Cek Kalender Libur
        $holiday = StoreHoliday::whereDate('holiday_date', $now->toDateString())->first();
        if ($holiday) {
            $isClosed = true;
            $closeReason = 'Dapur sedang libur: ' . $holiday->description;
        }

        // 2. Cek Jam Operasional (Dengan Fallback Default)
        if (!$isClosed) {
            $setting = StoreSetting::first();
            
            // Ambil dari database, kalau kosong anggap aja buka 08:00, tutup 17:00
            $startTime = $setting ? $setting->operational_time_start : '08:00:00';
            $endTime = $setting ? $setting->operational_time_end : '17:00:00';

            $start = Carbon::createFromTimeString($startTime, 'Asia/Jakarta');
            $end = Carbon::createFromTimeString($endTime, 'Asia/Jakarta');

            if (!$now->between($start, $end)) {
                $isClosed = true;
                $closeReason = 'Dapur kami beroperasi dari jam ' . $start->format('H:i') . ' - ' . $end->format('H:i') . ' WIB.';
            }
        }

        if ($isClosed) {
            return redirect()->route('toko.tutup')->with('close_reason', $closeReason);
        }

        return $next($request);
    }
}