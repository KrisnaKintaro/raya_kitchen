<?php

namespace App\Http\Controllers\Api\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting; // Pastikan model ini dipanggil
use Carbon\Carbon;
use Illuminate\Http\Request;

class PreorderController extends Controller
{
    public function getAvailableTimes()
    {
        // Ambil data jam buka & tutup dari tabel store_settings
        $setting = StoreSetting::first();
        
        // Ambil format jam (misal: "08:00" dan "17:00")
        $openTimeStr = $setting->open_time ?? '08:00';
        $closeTimeStr = $setting->close_time ?? '17:00';

        $openTime = Carbon::createFromFormat('H:i', $openTimeStr);
        $closeTime = Carbon::createFromFormat('H:i', $closeTimeStr);

        $times = [];
        $current = clone $openTime;

        // Looping jam dari jam buka sampai jam tutup
        while ($current <= $closeTime) {
            $times[] = $current->format('H:i') . ' WIB';
            $current->addHour(); // Interval penambahan waktu per 1 jam
        }

        return response()->json([
            'success' => true,
            'available_times' => $times
        ]);
    }

    // Fungsi heatmap atau fungsi lainnya biarkan tetap ada di bawahnya...
}