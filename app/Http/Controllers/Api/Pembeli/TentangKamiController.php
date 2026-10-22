<?php

namespace App\Http\Controllers\Api\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use Illuminate\Http\Request;

class TentangKamiController extends Controller
{
    public function index()
    {
        // Ambil data pertama karena profil perusahaan cuma butuh 1 baris
        $profile = CompanyProfile::first();

        if (!$profile) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data profil perusahaan belum tersedia.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $profile
        ], 200);
    }
}