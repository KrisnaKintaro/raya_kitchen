<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\StoreHoliday;
use App\Models\StoreSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OperasionalLiburController extends Controller
{
    private const VALID_COURIERS = [
        'jne', 'sicepat', 'ide', 'sap', 'jnt', 'ninja', 'tiki', 'lion',
        'anteraja', 'pos', 'ncs', 'rex', 'rpx', 'sentral', 'star', 'wahana', 'spx'
    ];

    /**
     * Mengambil pengaturan operasional toko
     * GET /api/admin/operasional
     */
    public function getOperasional()
    {
        $setting = StoreSetting::first();

        if (!$setting) {
            $setting = StoreSetting::create([
                'max_qty_per_day'            => 60,
                'max_orders_per_hour'        => 5,
                'operational_time_start'     => '08:00:00',
                'operational_time_end'       => '17:00:00',
                'free_delivery_max_distance' => 10,
                'active_couriers'            => 'jne,sicepat,jnt',
            ]);
        }

        $data = $setting->toArray();
        $data['operational_time_start']     = date('H:i', strtotime($setting->operational_time_start ?? '08:00:00'));
        $data['operational_time_end']       = date('H:i', strtotime($setting->operational_time_end ?? '17:00:00'));
        $data['free_delivery_max_distance'] = (float) ($setting->free_delivery_max_distance ?? 10);
        $data['active_couriers']            = $this->sanitizeCouriers($setting->active_couriers ?? 'jne,sicepat,jnt');

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    /**
     * Menyimpan perubahan pengaturan operasional toko
     * POST /api/admin/operasional
     */
    public function updateOperasional(Request $request)
    {
        $validated = $request->validate([
            'max_qty_per_day'            => 'required|integer|min:1|max:10000',
            'max_orders_per_hour'        => 'required|integer|min:1|max:500',
            'operational_time_start'     => 'required|string',
            'operational_time_end'       => 'required|string',
            'free_delivery_max_distance' => 'required|numeric|min:0|max:500',
            'active_couriers'            => 'nullable|string',
        ], [
            'max_qty_per_day.required'            => 'Batas maksimal produksi per hari wajib diisi.',
            'max_orders_per_hour.required'        => 'Batas antrean kirim per jam wajib diisi.',
            'operational_time_start.required'     => 'Jam buka operasional wajib diisi.',
            'operational_time_end.required'       => 'Jam tutup operasional wajib diisi.',
            'free_delivery_max_distance.required' => 'Batas jarak gratis ongkir wajib diisi.',
        ]);

        $startHour = (int) substr($validated['operational_time_start'], 0, 2);
        $endHour   = (int) substr($validated['operational_time_end'], 0, 2);

        if ($endHour <= $startHour) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Jam tutup operasional harus lebih besar dari jam buka.',
            ], 422);
        }

        $cleanCouriers = $this->sanitizeCouriers($validated['active_couriers'] ?? '');
        if ($cleanCouriers === '') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pilih minimal 1 kurir ekspedisi aktif untuk pengiriman luar jangkauan.',
            ], 422);
        }

        $startTimeFormatted = strlen($validated['operational_time_start']) === 5
            ? $validated['operational_time_start'] . ':00'
            : $validated['operational_time_start'];

        $endTimeFormatted = strlen($validated['operational_time_end']) === 5
            ? $validated['operational_time_end'] . ':00'
            : $validated['operational_time_end'];

        $setting = StoreSetting::first() ?? new StoreSetting();
        $setting->fill([
            'max_qty_per_day'            => (int) $validated['max_qty_per_day'],
            'max_orders_per_hour'        => (int) $validated['max_orders_per_hour'],
            'operational_time_start'     => $startTimeFormatted,
            'operational_time_end'       => $endTimeFormatted,
            'free_delivery_max_distance' => (float) $validated['free_delivery_max_distance'],
            'active_couriers'            => $cleanCouriers,
        ]);
        $setting->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengaturan operasional berhasil disimpan!',
            'data'    => [
                'max_qty_per_day'            => $setting->max_qty_per_day,
                'max_orders_per_hour'        => $setting->max_orders_per_hour,
                'operational_time_start'     => date('H:i', strtotime($setting->operational_time_start)),
                'operational_time_end'       => date('H:i', strtotime($setting->operational_time_end)),
                'free_delivery_max_distance' => (float) $setting->free_delivery_max_distance,
                'active_couriers'            => $setting->active_couriers,
            ],
        ]);
    }

    /**
     * Mengambil daftar hari libur beserta metadata tanggal & jumlah pesanan aktif
     * GET /api/admin/libur
     */
    public function getLibur()
    {
        $todayStr = now()->toDateString();
        $holidays = StoreHoliday::orderBy('holiday_date', 'asc')->get();

        // Hitung pesanan aktif yang tanggal kirimnya bertepatan dengan tanggal libur
        $holidayDates = $holidays->pluck('holiday_date')->all();
        $ordersByDate = [];

        if (!empty($holidayDates)) {
            $ordersByDate = Order::whereIn('delivery_date', $holidayDates)
                ->where('status', '!=', 'dibatalkan')
                ->selectRaw('delivery_date, count(*) as total')
                ->groupBy('delivery_date')
                ->pluck('total', 'delivery_date')
                ->all();
        }

        $formatted = $holidays->map(function (StoreHoliday $item) use ($todayStr, $ordersByDate) {
            $dt = Carbon::parse($item->holiday_date)->locale('id');
            $dateStr = $dt->toDateString();

            $timeState = 'upcoming';
            if ($dateStr === $todayStr) {
                $timeState = 'today';
            } elseif ($dateStr < $todayStr) {
                $timeState = 'past';
            }

            $desc = $item->description ?: 'Libur Operasional Dapur';
            $category = 'manual';
            if (str_contains(strtolower($desc), 'akhir pekan')) {
                $category = 'weekend';
            } elseif (str_contains(strtolower($desc), 'nasional')) {
                $category = 'national';
            }

            return [
                'id'                  => $item->id,
                'holiday_date'        => $dateStr,
                'formatted_date'      => $dt->isoFormat('dddd, D MMM YYYY'),
                'year'                => (int) $dt->year,
                'description'         => $desc,
                'category'            => $category,
                'time_state'          => $timeState,
                'active_orders_count' => (int) ($ordersByDate[$dateStr] ?? 0),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data'   => $formatted,
            'meta'   => [
                'total_count'    => $formatted->count(),
                'upcoming_count' => $formatted->whereIn('time_state', ['today', 'upcoming'])->count(),
                'past_count'     => $formatted->where('time_state', 'past')->count(),
            ],
        ]);
    }

    /**
     * Menambahkan tanggal libur baru (atau mengupdate keterangan jika tanggal sudah ada)
     * POST /api/admin/libur
     */
    public function addLibur(Request $request)
    {
        $validated = $request->validate([
            'holiday_date' => 'required|date|unique:store_holidays,holiday_date',
            'description'  => 'required|string|max:255',
        ], [
            'holiday_date.required' => 'Tanggal libur wajib dipilih.',
            'holiday_date.unique'   => 'Tanggal tersebut sudah terdaftar di kalender libur.',
            'description.required'  => 'Keterangan hari libur wajib diisi.',
        ]);

        $holiday = StoreHoliday::create([
            'holiday_date' => Carbon::parse($validated['holiday_date'])->toDateString(),
            'description'  => trim($validated['description']),
        ]);

        $activeOrders = Order::whereDate('delivery_date', $holiday->holiday_date)
            ->where('status', '!=', 'dibatalkan')
            ->count();

        $msg = 'Hari libur berhasil ditambahkan!';
        if ($activeOrders > 0) {
            $msg .= " Perhatian: Terdapat {$activeOrders} pesanan aktif pada tanggal ini.";
        }

        return response()->json([
            'status'  => 'success',
            'message' => $msg,
        ]);
    }

    /**
     * Menghapus satu tanggal libur berdasarkan ID
     * DELETE /api/admin/libur/{id}
     */
    public function deleteLibur($id)
    {
        $holiday = StoreHoliday::find($id);
        if (!$holiday) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data hari libur tidak ditemukan.',
            ], 404);
        }

        $holiday->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Tanggal libur berhasil dihapus dari kalender!',
        ]);
    }

    /**
     * Menghapus seluruh tanggal libur yang sudah lewat dari hari ini
     * DELETE /api/admin/libur-past/clear
     */
    public function clearPastLibur()
    {
        $deleted = StoreHoliday::whereDate('holiday_date', '<', now()->toDateString())->delete();

        return response()->json([
            'status'  => 'success',
            'message' => $deleted > 0
                ? "{$deleted} tanggal libur yang sudah lewat berhasil dibersihkan!"
                : 'Tidak ada tanggal libur lampau yang perlu dibersihkan.',
        ]);
    }

    /**
     * Generate otomatis hari libur akhir pekan & libur nasional berdasarkan tahun & mode
     * POST /api/admin/libur/generate
     */
    public function generateLiburOtomatis(Request $request)
    {
        $year = (int) $request->input('year', date('Y'));
        if ($year < 2024 || $year > 2035) {
            $year = (int) date('Y');
        }

        $mode = $request->input('mode', 'all'); // all, weekend_only, national_only
        $includeWeekend  = in_array($mode, ['all', 'weekend_only']);
        $includeNational = in_array($mode, ['all', 'national_only']);

        $startDate = Carbon::create($year, 1, 1)->startOfDay();
        $endDate   = Carbon::create($year, 12, 31)->endOfDay();
        $insertedCount = 0;

        if ($includeWeekend) {
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                if ($date->isWeekend()) {
                    $hari = $date->isSaturday() ? 'Sabtu' : 'Minggu';
                    $holiday = StoreHoliday::firstOrCreate(
                        ['holiday_date' => $date->toDateString()],
                        ['description' => "Libur Akhir Pekan ({$hari})"]
                    );
                    if ($holiday->wasRecentlyCreated) {
                        $insertedCount++;
                    }
                }
            }
        }

        if ($includeNational) {
            try {
                $response = Http::timeout(6)->get("https://api-harilibur.vercel.app/api?year={$year}");

                if ($response->successful() && is_array($response->json())) {
                    foreach ($response->json() as $holiday) {
                        if (!empty($holiday['is_national_holiday']) && !empty($holiday['holiday_date'])) {
                            $parsedDate = Carbon::parse($holiday['holiday_date'])->toDateString();
                            $hol = StoreHoliday::firstOrCreate(
                                ['holiday_date' => $parsedDate],
                                ['description' => 'Libur Nasional: ' . ($holiday['holiday_name'] ?? 'Hari Raya')]
                            );
                            if ($hol->wasRecentlyCreated) {
                                $insertedCount++;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Jika API eksternal timeout, tetap lanjutkan hasil yang sudah berhasil disimpan
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => "{$insertedCount} tanggal libur baru berhasil ditambahkan untuk tahun {$year}!",
        ]);
    }

    /**
     * Membersihkan daftar kode kurir agar hanya berisi kode valid RajaOngkir
     */
    private function sanitizeCouriers(?string $rawCouriers): string
    {
        if (empty($rawCouriers)) {
            return '';
        }

        return collect(explode(',', $rawCouriers))
            ->map(fn ($c) => strtolower(trim($c)))
            ->filter(fn ($c) => in_array($c, self::VALID_COURIERS, true))
            ->unique()
            ->values()
            ->implode(',');
    }
}