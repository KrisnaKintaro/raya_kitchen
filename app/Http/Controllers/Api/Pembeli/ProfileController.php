<?php

namespace App\Http\Controllers\Api\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    // Mengambil data profil dan alamat user
    public function edit()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized, silahkan login terlebih dahulu.'
            ], 401);
        }

        $wa = $user->whatsapp_number ?? '';
        if (str_starts_with($wa, '62')) {
            $wa = '0' . substr($wa, 2);
        }

        $primaryAddress = UserAddress::where('user_id', $user->id)
            ->where('is_primary', 1)
            ->first() ?? UserAddress::where('user_id', $user->id)->first();

        $allAddresses = UserAddress::where('user_id', $user->id)->orderBy('is_primary', 'desc')->get();

        $allAddresses->transform(function ($address) {
            if (str_starts_with($address->recipient_whatsapp, '62')) {
                $address->recipient_whatsapp = '0' . substr($address->recipient_whatsapp, 2);
            }
            return $address;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Data profil berhasil dimuat',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'whatsapp_number' => $wa,
                ],
                'address' => $primaryAddress ? [
                    'id' => $primaryAddress->id,
                    'label' => $primaryAddress->label,
                    'province' => $primaryAddress->province,
                    'city' => $primaryAddress->city,
                    'district' => $primaryAddress->district,
                    'postal_code' => $primaryAddress->postal_code,
                    'street_address' => $primaryAddress->street_address,
                    'landmark' => $primaryAddress->landmark,
                    'is_primary' => $primaryAddress->is_primary,
                    'latitude' => $primaryAddress->latitude,
                    'longitude' => $primaryAddress->longitude,
                    'distance_to_store' => $primaryAddress->distance_to_store,
                ] : null,
                'all_addresses' => $allAddresses
            ]
        ], 200);
    }

    public function hapusAlamat($id)
    {
        $address = UserAddress::where('user_id', Auth::id())->find($id);

        if (!$address) {
            return response()->json(['status' => 'error', 'message' => 'Alamat tidak ditemukan.'], 404);
        }

        $address->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Alamat berhasil dihapus.'
        ]);
    }

    // Menyimpan / Memperbarui alamat menggunakan metode POST
    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized, silahkan login terlebih dahulu.'
            ], 401);
        }

        try {
            $addressId = $request->input('address_id');
            $isPrimary = $request->has('is_primary') ? (int)$request->input('is_primary') : 0;

            if ($isPrimary == 1) {
                UserAddress::where('user_id', $user->id)->update(['is_primary' => 0]);
            }

            // Jika user belum punya alamat sama sekali, jadikan alamat pertama ini sebagai utama otomatis
            if (UserAddress::where('user_id', $user->id)->count() === 0) {
                $isPrimary = 1;
            }

            // Hitung Jarak ke Toko (Raya Kitchen) menggunakan Haversine
            $dapur = CompanyProfile::first();
            $jarak = 0;

            if ($dapur && $dapur->latitude && $dapur->longitude && $request->filled('latitude') && $request->filled('longitude')) {
                $jarak = $this->hitungJarak($dapur->latitude, $dapur->longitude, $request->input('latitude'), $request->input('longitude'));
            }

            // ==========================================
            // AUTO-MATCH RAJAONGKIR ID
            // ==========================================
            $roId = null;
            if ($request->filled('district') && $request->filled('city')) {
                $roId = $this->getRajaOngkirId($request->input('district'), $request->input('city'));
            }

            $addressData = [
                'user_id' => $user->id,
                'label' => $request->input('label') ?? 'Rumah',
                'recipient_name' => $user->name,
                'recipient_whatsapp' => $user->whatsapp_number,
                'province' => $request->input('province'),
                'city' => $request->input('city'),
                'district' => $request->input('district'),
                'postal_code' => $request->input('postal_code'),
                'street_address' => $request->input('street_address'),
                'landmark' => $request->input('landmark'),
                'is_primary' => $isPrimary,
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'distance_to_store' => $jarak,
                'rajaongkir_destination_id' => $roId, // <-- Simpan ID RajaOngkir ke DB
            ];

            // Cek apakah ini proses update atau tambah baru (insert)
            if (!empty($addressId) && is_numeric($addressId)) {
                $address = UserAddress::where('id', $addressId)->where('user_id', $user->id)->first();
                if ($address) {
                    $address->update($addressData);
                } else {
                    $address = UserAddress::create($addressData);
                }
            } else {
                $address = UserAddress::create($addressData);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Alamat berhasil disimpan!',
                'data' => [
                    'address' => $address
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Gagal menyimpan alamat: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fungsi Helper untuk Auto-Match ID Destinasi RajaOngkir (Komerce)
     */
    private function getRajaOngkirId($district, $city)
    {
        try {
            $response = Http::withHeaders([
                'key' => env('RAJAONGKIR_API_KEY')
            ])->get('https://rajaongkir.komerce.id/api/v1/destination/domestic-destination', [
                'search' => $district
            ]);

            if ($response->successful() && isset($response->json()['data'])) {
                // Bersihkan kata "Kota" atau "Kabupaten" dari input frontend biar match
                $cityClean = strtolower(str_replace(['kota ', 'kabupaten ', 'kab. '], '', $city));

                foreach ($response->json()['data'] as $dest) {
                    // Pastikan kotanya cocok, karena nama kecamatan bisa kembar di beda provinsi/kota
                    if (str_contains(strtolower($dest['city_name']), $cityClean)) {
                        return $dest['id'];
                    }
                }
                // Fallback: Ambil hasil pertama kalau gagal match kota persis (tapi kecamatannya ketemu)
                return $response->json()['data'][0]['id'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('Gagal fetch RajaOngkir ID: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Rumus Haversine untuk hitung jarak lurus (KM) antara 2 titik koordinat
     */
    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Jari-jari bumi dalam kilometer

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * asin(sqrt($a));
        $distance = $earthRadius * $c;

        return round($distance, 2); // Dibulatkan 2 angka di belakang koma
    }

    public function updateProfilePribadi(Request $request)
    {
        $user = $request->user();

        // 1. Normalisasi nomor WA terlebih dahulu dan masukkan kembali ke dalam Request
        $waNumber = preg_replace('/[^0-9]/', '', $request->whatsapp_number);
        if (str_starts_with($waNumber, '0')) {
            $waNumber = '62' . substr($waNumber, 1);
        } elseif (!str_starts_with($waNumber, '62')) {
            $waNumber = '62' . $waNumber;
        }

        $request->merge(['whatsapp_number' => $waNumber]);

        // 2. Validasi dengan Rule::unique bawaan Laravel untuk mengecek duplikasi
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'whatsapp_number.unique' => 'Nomor WhatsApp sudah digunakan oleh akun lain.'
        ]);

        // 3. Hash password hanya jika diisi oleh user
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            // Hapus key password dari array agar tidak meng-update field dengan nilai kosong
            unset($validated['password']);
        }

        // 4. Update data secara langsung menggunakan Eloquent Mass Assignment
        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui!'
        ]);
    }
}
