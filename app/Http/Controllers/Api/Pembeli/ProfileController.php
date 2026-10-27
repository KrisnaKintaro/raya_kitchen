<?php

namespace App\Http\Controllers\Api\Pembeli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
                ] : null,
                'all_addresses' => $allAddresses
            ]
        ], 200);
    }

    public function hapusAlamat($id)
    {
        $address = auth()->user()->addresses()->find($id);
        
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
            ];

            // Cek apakah ini proses update (jika address_id ada dan berupa angka) atau tambah baru (insert)
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
}