<?php

namespace App\Http\Controllers\Api\Pembeli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KatalogController extends Controller
{
    public function index(Request $request)
    {
        $produk = [
            [
                'id' => 1,
                'nama' => 'Sepatu Sneakers Casual White',
                'slug' => 'sepatu-sneakers-casual-white',
                'harga' => 250000,
                'kategori' => 'Fashion',
                'gambar' => 'https://via.placeholder.com/300x300?text=Sepatu+Casual',
            ],
            [
                'id' => 2,
                'nama' => 'Kemeja Flannel Oversize',
                'slug' => 'kemeja-flannel-oversize',
                'harga' => 135000,
                'kategori' => 'Pakaian',
                'gambar' => 'https://via.placeholder.com/300x300?text=Kemeja+Flannel',
            ],
            [
                'id' => 3,
                'nama' => 'Tas Backpack Laptop 15 Inch',
                'slug' => 'tas-backpack-laptop-15-inch',
                'harga' => 185000,
                'kategori' => 'Aksesoris',
                'gambar' => 'https://via.placeholder.com/300x300?text=Tas+Backpack',
            ],
            [
                'id' => 4,
                'nama' => 'Jam Tangan Minimalis Water Resistant',
                'slug' => 'jam-tangan-minimalis',
                'harga' => 320000,
                'kategori' => 'Aksesoris',
                'gambar' => 'https://via.placeholder.com/300x300?text=Jam+Tangan',
            ]
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Data katalog berhasil dimuat',
            'data' => $produk
        ], 200);
    }
}
