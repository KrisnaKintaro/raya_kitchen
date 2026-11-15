<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\StoreSetting;
use App\Models\StoreHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class SystemStoreController extends Controller
{
    // ==========================================
    // PROFIL PERUSAHAAN & LOKASI
    // ==========================================
public function getProfil()
    {
        $profil = CompanyProfile::first();
        $setting = StoreSetting::first();

        $data = $profil ? $profil->toArray() : [];
        if (!empty($data['image_url']) && !str_starts_with($data['image_url'], 'http')) {
            $clean = preg_replace('#^/?(storage/)?(profil/)?#i', '', trim($data['image_url']));
            $data['image_full_url'] = asset('storage/profil/' . $clean);
        } else {
            $data['image_full_url'] = $data['image_url'] ?? null;
        }
        $data['free_delivery_max_distance'] = $setting ? $setting->free_delivery_max_distance : 10;

        return response()->json(['status' => 'success', 'data' => $data]);
    }
    public function updateProfil(Request $request)
    {
        $profil = CompanyProfile::first() ?? new CompanyProfile();
        $data = $request->except(['image_url', 'free_delivery_max_distance']);

        if ($request->hasFile('image_url')) {
            if ($profil->image_url && !str_starts_with($profil->image_url, 'http')) {
                $oldClean = preg_replace('#^/?(storage/)?(profil/)?#i', '', trim($profil->image_url));
                if (Storage::disk('public')->exists('profil/' . $oldClean)) {
                    Storage::disk('public')->delete('profil/' . $oldClean);
                }
            }

            $file = $request->file('image_url');
            $fileName = 'profil_' . time() . '.' . $file->getClientOriginalExtension();
            // Otomatis membuat folder storage/app/public/profil jika belum ada
            $file->storeAs('profil', $fileName, 'public');
            $data['image_url'] = $fileName;
        }

        if ($request->filled('district') && $request->filled('city')) {
            $roId = $this->getRajaOngkirId($request->input('district'), $request->input('city'));
            $data['rajaongkir_destination_id'] = $roId;
        }

        $profil->fill($data);
        $profil->save();

        if ($request->has('free_delivery_max_distance')) {
            $setting = StoreSetting::first() ?? new StoreSetting();
            $setting->free_delivery_max_distance = $request->input('free_delivery_max_distance');
            $setting->save();
        }

        return response()->json(['status' => 'success', 'message' => 'Profil dan Lokasi berhasil diperbarui!']);
    }

    private function getRajaOngkirId($district, $city)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'key' => env('RAJAONGKIR_API_KEY')
            ])->get('https://rajaongkir.komerce.id/api/v1/destination/domestic-destination', [
                'search' => $district
            ]);

            if ($response->successful() && isset($response->json()['data'])) {
                // Bersihkan kata "Kota" atau "Kabupaten" dari input admin (jika ada) biar match
                $cityClean = strtolower(str_replace(['kota ', 'kabupaten ', 'kab. '], '', $city));

                foreach ($response->json()['data'] as $dest) {
                    if (str_contains(strtolower($dest['city_name']), $cityClean)) {
                        return $dest['id'];
                    }
                }
                // Fallback: Ambil hasil pertama kalau gagal match kota
                return $response->json()['data'][0]['id'] ?? null;
            }
        } catch (\Exception $e) {}

        return null;
    }

    // ==========================================
    // OPERASIONAL & LIBUR
    // ==========================================
    public function getOperasional()
    {
        $setting = StoreSetting::first();

        if (!$setting) {
            $setting = StoreSetting::create([
                'max_qty_per_day' => 60,
                'max_orders_per_hour' => 5,
                'operational_time_start' => '08:00:00',
                'operational_time_end' => '17:00:00',
                'free_delivery_max_distance' => 10,
            ]);
        }

        $data = $setting->toArray();
        $data['operational_time_start'] = date('H:i', strtotime($setting->operational_time_start));
        $data['operational_time_end'] = date('H:i', strtotime($setting->operational_time_end));

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function updateOperasional(Request $request)
    {
        $setting = StoreSetting::first();
        if (!$setting) {
            $setting = new StoreSetting();
        }

        $setting->update($request->all());
        return response()->json(['status' => 'success', 'message' => 'Pengaturan operasional tersimpan!']);
    }

    public function getLibur()
    {
        $libur = StoreHoliday::orderBy('holiday_date', 'asc')->get();
        return response()->json(['status' => 'success', 'data' => $libur]);
    }

    public function addLibur(Request $request)
    {
        $request->validate([
            'holiday_date' => 'required|date|unique:store_holidays,holiday_date',
            'description' => 'nullable|string'
        ]);

        StoreHoliday::create($request->all());
        return response()->json(['status' => 'success', 'message' => 'Hari libur berhasil ditambahkan!']);
    }

    public function deleteLibur($id)
    {
        StoreHoliday::destroy($id);
        return response()->json(['status' => 'success', 'message' => 'Hari libur dihapus!']);
    }

    public function generateLiburOtomatis(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $startDate = Carbon::create($year, 1, 1);
        $endDate = Carbon::create($year, 12, 31);
        $insertedCount = 0;

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if ($date->isWeekend()) {
                $hari = $date->isSaturday() ? 'Sabtu' : 'Minggu';
                $holiday = StoreHoliday::firstOrCreate(
                    ['holiday_date' => $date->toDateString()],
                    ['description' => "Libur Akhir Pekan ($hari)"]
                );
                if ($holiday->wasRecentlyCreated) $insertedCount++;
            }
        }

        try {
            $response = Http::timeout(5)->get("https://api-harilibur.vercel.app/api?year={$year}");

            if ($response->successful()) {
                $holidays = $response->json();
                foreach ($holidays as $holiday) {
                    if (isset($holiday['is_national_holiday']) && $holiday['is_national_holiday']) {
                        $hol = StoreHoliday::firstOrCreate(
                            ['holiday_date' => $holiday['holiday_date']],
                            ['description' => 'Libur Nasional: ' . $holiday['holiday_name']]
                        );
                        if ($hol->wasRecentlyCreated) $insertedCount++;
                    }
                }
            }
        } catch (\Exception $e) {}

        return response()->json([
            'status' => 'success',
            'message' => "$insertedCount hari libur berhasil ditambahkan otomatis untuk tahun $year!"
        ]);
    }

    // ==========================================
    // KELOLA PENGIRIMAN
    // ==========================================
    public function getPengiriman()
    {
        $orders = \App\Models\Order::with('userAddress')->where('status', 'Selesai')->get();
        $internal = [];
        $ekspedisi = [];

        foreach ($orders as $order) {
            if ($order->courier_name == 'Kurir Internal') {
                $internal[] = $order;
            } else {
                $ekspedisi[] = $order;
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'internal' => $internal,
                'ekspedisi' => $ekspedisi
            ]
        ]);
    }

    // public function prosesKirim(Request $request, $id)
    // {
    //     $order = \App\Models\Order::find($id);
    //     if (!$order) return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan'], 404);

    //     $order->status = 'Dikirim';
    //     if ($request->has('tracking_number')) {
    //         $order->tracking_number = $request->tracking_number;
    //     }
    //     $order->save();

    //     OrderTracking::create([
    //         'order_id' => $order->id,
    //         'status' => 'Pesanan sedang dalam perjalanan',
    //         'latitude' => \App\Models\CompanyProfile::first()->latitude ?? null,
    //         'longitude' => \App\Models\CompanyProfile::first()->longitude ?? null,
    //     ]);

    //     return response()->json(['status' => 'success', 'message' => 'Status pesanan berhasil diubah menjadi Dikirim!']);
    // }
}
