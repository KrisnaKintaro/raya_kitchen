<?php

namespace App\Http\Controllers\Api\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;

class TentangKamiController extends Controller
{
    public static function formatCompanyImageUrl(?string $rawPath): ?string
    {
        if (empty($rawPath)) {
            return null;
        }
        if (str_starts_with($rawPath, 'http://') || str_starts_with($rawPath, 'https://')) {
            return $rawPath;
        }
        $clean = preg_replace('#^/?(storage/)?(profil/)?#i', '', trim($rawPath));
        return asset('storage/profil/' . $clean);
    }

    public function index()
    {
        $profile = CompanyProfile::first();

        if (!$profile) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data profil perusahaan belum tersedia.'
            ], 404);
        }

        $data = $profile->toArray();
        $data['image_url'] = self::formatCompanyImageUrl($profile->image_url);

        return response()->json([
            'status' => 'success',
            'data'   => $data
        ], 200);
    }
}