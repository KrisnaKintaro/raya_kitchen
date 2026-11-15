<?php

namespace App\Http\Controllers\Api\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class KatalogController extends Controller
{
    /**
     * Normalisasi URL gambar produk ke storage/app/public/produk
     */
    public static function formatProductImageUrl(?string $rawPath): string
    {
        if (empty($rawPath)) {
            return asset('asset/logo_app.png');
        }
        if (str_starts_with($rawPath, 'http://') || str_starts_with($rawPath, 'https://')) {
            return $rawPath;
        }
        // Bersihkan prefix berulang seperti /storage/ atau produk/ agar tidak dobel
        $clean = preg_replace('#^/?(storage/)?(produk/)?#i', '', trim($rawPath));
        return asset('storage/produk/' . $clean);
    }

    public function index(Request $request)
    {
        $categories = Product::where('is_active', true)
            ->select('id', 'name')
            ->get();

        $variants = ProductVariant::with('product:id,name')
            ->whereHas('product', function ($q) {
                $q->where('is_active', true);
            })
            ->get()
            ->map(function ($variant) {
                return [
                    'id'            => $variant->id,
                    'product_id'    => $variant->product_id,
                    'variant_name'  => $variant->variant_name,
                    'price'         => $variant->price,
                    'image_url'     => self::formatProductImageUrl($variant->image_url),
                    'category_name' => $variant->product->name ?? 'Uncategorized',
                ];
            });

        return response()->json([
            'status'  => 'success',
            'message' => 'Data katalog berhasil dimuat',
            'data'    => [
                'categories' => $categories,
                'variants'   => $variants
            ]
        ], 200);
    }

    public function show($id)
    {
        $variant = ProductVariant::with('product')->find($id);

        if (!$variant) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Waduh, produk tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'        => $variant->id,
                'nama'      => $variant->variant_name,
                'kategori'  => $variant->product->name ?? 'Kategori Umum',
                'deskripsi' => $variant->product->description ?? 'Deskripsi roti belum tersedia.',
                'harga'     => $variant->price,
                'gambar'    => self::formatProductImageUrl($variant->image_url),
            ]
        ], 200);
    }
}