<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProfilPerusahaanController extends Controller
{
    /**
     * Normalisasi format nomor WhatsApp (08xxx -> 628xxx)
     */
    private function formatWhatsapp($number)
    {
        $clean = preg_replace('/[^0-9]/', '', $number);
        if (str_starts_with($clean, '0')) {
            return '62' . substr($clean, 1);
        }
        return $clean;
    }

    /**
     * Helper mencari ID Destinasi RajaOngkir Komerce berdasarkan kata kunci wilayah/kecamatan/kota
     */
    private function resolveRajaOngkirId($keyword)
    {
        if (empty($keyword) || !env('RAJAONGKIR_API_KEY')) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'key' => env('RAJAONGKIR_API_KEY')
            ])->get('https://rajaongkir.komerce.id/api/v1/destination/domestic-destination', [
                'search' => $keyword
            ]);

            if ($response->successful() && !empty($response->json()['data'])) {
                return $response->json()['data'][0]['id'] ?? null;
            }
        } catch (\Exception $e) {
            Log::warning('Gagal auto-resolve RajaOngkir ID Dapur: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * GET /api/admin/profil-perusahaan
     */
    public function show()
    {
        $profil = CompanyProfile::first();

        if (!$profil) {
            return response()->json([
                'status' => 'success',
                'data' => null
            ]);
        }

        $data = $profil->toArray();
        $data['image_full_url'] = $profil->image_url
            ? asset('storage/' . $profil->image_url)
            : null;

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * POST /api/admin/profil-perusahaan
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name'              => 'required|string|max:255',
            'established_year'          => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'whatsapp_number'           => 'required|string|max:30',
            'email_address'             => 'required|email|max:255',
            'instagram_handle'          => 'nullable|string|max:100',
            'short_description'         => 'nullable|string',
            'background_history'        => 'nullable|string',
            'address'                   => 'required|string',
            'map_external_url'          => 'nullable|string',
            'map_embed_url'             => 'nullable|string',
            'latitude'                  => 'required|numeric',
            'longitude'                 => 'required|numeric',
            'rajaongkir_keyword'        => 'nullable|string|max:150',
            'rajaongkir_destination_id' => 'nullable|string|max:50',
            'image_url'                 => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $profil = CompanyProfile::first() ?? new CompanyProfile();

        // Format nomor WhatsApp
        $validated['whatsapp_number'] = $this->formatWhatsapp($validated['whatsapp_number']);

        // Handle Upload Gambar ke storage/app/public/profil
        if ($request->hasFile('image_url')) {
            if ($profil->image_url && Storage::disk('public')->exists($profil->image_url)) {
                Storage::disk('public')->delete($profil->image_url);
            }
            $validated['image_url'] = $request->file('image_url')->store('profil', 'public');
        } else {
            unset($validated['image_url']);
        }

        // Resolve ID RajaOngkir Dapur agar perhitungan ongkir ekspedisi tetap akurat
        if (!empty($validated['rajaongkir_keyword'])) {
            $matchedId = $this->resolveRajaOngkirId($validated['rajaongkir_keyword']);
            if ($matchedId) {
                $validated['rajaongkir_destination_id'] = $matchedId;
            }
        } elseif (empty($validated['rajaongkir_destination_id']) && empty($profil->rajaongkir_destination_id)) {
            // Coba ambil kata terakhir dari alamat (biasanya nama kota/kecamatan)
            $parts = array_filter(array_map('trim', explode(',', $validated['address'])));
            $lastPart = end($parts);
            if ($lastPart) {
                $matchedId = $this->resolveRajaOngkirId($lastPart);
                if ($matchedId) {
                    $validated['rajaongkir_destination_id'] = $matchedId;
                }
            }
        }

        unset($validated['rajaongkir_keyword']);

        $profil->fill($validated);
        $profil->save();

        $resData = $profil->toArray();
        $resData['image_full_url'] = $profil->image_url
            ? asset('storage/' . $profil->image_url)
            : null;

        return response()->json([
            'status' => 'success',
            'message' => 'Profil perusahaan dan koordinat dapur berhasil disimpan!',
            'data' => $resData
        ]);
    }
}