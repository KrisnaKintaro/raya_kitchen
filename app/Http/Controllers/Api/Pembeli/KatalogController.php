<?php

namespace App\Http\Controllers\Api\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class KatalogController extends Controller
{
    public function index(Request $request)
    {
        // Ambil semua kategori induk yang aktif
        $categories = Product::where('is_active', true)
            ->select('id', 'name')
            ->get();

        // Ambil semua varian roti beserta nama kategorinya
        $variants = ProductVariant::with('product:id,name')
            ->whereHas('product', function($q) {
                $q->where('is_active', true);
            })
            ->get()
            ->map(function($variant) {
                return [
                    'id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'variant_name' => $variant->variant_name,
                    'price' => $variant->price,
                    'image_url' => $variant->image_url,
                    'category_name' => $variant->product->name ?? 'Uncategorized',
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Data katalog berhasil dimuat',
            'data' => [
                'categories' => $categories,
                'variants' => $variants
            ]
        ], 200);
    }
}
