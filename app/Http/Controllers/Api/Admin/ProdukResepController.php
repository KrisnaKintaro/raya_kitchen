<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProdukResepController extends Controller
{
    /**
     * Helper format URL gambar agar mendukung path storage lokal maupun URL eksternal
     */
    private function resolveImageUrl($path)
    {
        if (empty($path)) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return asset('storage/' . ltrim($path, '/'));
    }

    // =========================================================================
    // 1. KATALOG PRODUK & VARIAN
    // =========================================================================

    public function indexProduk()
    {
        $products = Product::with(['variants' => function ($q) {
                $q->orderBy('id', 'asc');
            }])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($product) {
                return [
                    'id'          => $product->id,
                    'name'        => $product->name,
                    'description' => $product->description,
                    'is_active'   => (bool) $product->is_active,
                    'variants'    => $product->variants->map(function ($v) {
                        return [
                            'id'           => $v->id,
                            'product_id'   => $v->product_id,
                            'variant_name' => $v->variant_name,
                            'price'        => (float) $v->price,
                            'image_url'    => $this->resolveImageUrl($v->image_url),
                        ];
                    })->values(),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => $products
        ]);
    }

    public function storeProduk(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $product = Product::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active'   => true,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Produk "' . $product->name . '" berhasil ditambahkan!',
            'data'    => $product
        ]);
    }

    public function updateProduk(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $product->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data produk "' . $product->name . '" berhasil diperbarui!',
            'data'    => $product
        ]);
    }

    public function toggleProduk($id)
    {
        $product = Product::findOrFail($id);
        $product->update([
            'is_active' => !$product->is_active
        ]);

        $statusTeks = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'status'    => 'success',
            'message'   => "Produk \"{$product->name}\" berhasil {$statusTeks}.",
            'is_active' => (bool) $product->is_active
        ]);
    }

    public function destroyProduk($id)
    {
        $product = Product::with('variants')->findOrFail($id);

        foreach ($product->variants as $variant) {
            if ($variant->image_url && !str_starts_with($variant->image_url, 'http') && Storage::disk('public')->exists($variant->image_url)) {
                Storage::disk('public')->delete($variant->image_url);
            }
            $variant->delete();
        }

        $product->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Produk beserta seluruh variannya berhasil dihapus.'
        ]);
    }

    // --- CRUD VARIAN PRODUK ---

    public function storeVarian(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $validated = $request->validate([
            'variant_name' => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            // Simpan ke storage/app/public/produk
            $imagePath = $request->file('image')->store('produk', 'public');
        }

        $variant = ProductVariant::create([
            'product_id'   => $product->id,
            'variant_name' => $validated['variant_name'],
            'price'        => $validated['price'],
            'image_url'    => $imagePath,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Varian "' . $variant->variant_name . '" berhasil ditambahkan!',
            'data'    => $variant
        ]);
    }

    public function updateVarian(Request $request, $variantId)
    {
        $variant = ProductVariant::findOrFail($variantId);

        $validated = $request->validate([
            'variant_name' => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $imagePath = $variant->image_url;
        if ($request->hasFile('image')) {
            if ($imagePath && !str_starts_with($imagePath, 'http') && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            // Simpan ke storage/app/public/produk
            $imagePath = $request->file('image')->store('produk', 'public');
        }

        $variant->update([
            'variant_name' => $validated['variant_name'],
            'price'        => $validated['price'],
            'image_url'    => $imagePath,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Varian "' . $variant->variant_name . '" berhasil diperbarui!',
            'data'    => $variant
        ]);
    }

    public function destroyVarian($variantId)
    {
        $variant = ProductVariant::findOrFail($variantId);

        if ($variant->image_url && !str_starts_with($variant->image_url, 'http') && Storage::disk('public')->exists($variant->image_url)) {
            Storage::disk('public')->delete($variant->image_url);
        }

        $variant->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Varian produk berhasil dihapus.'
        ]);
    }

    // =========================================================================
    // 2. RESEP DIGITAL DAPUR
    // =========================================================================

    public function indexResep()
    {
        $recipes = Recipe::orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'id'           => $r->id,
                    'title'        => $r->title,
                    'image_url'    => $this->resolveImageUrl($r->image_url),
                    'ingredients'  => is_array($r->ingredients) ? $r->ingredients : [],
                    'instructions' => $r->instructions,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => $recipes
        ]);
    }

    public function storeResep(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'ingredients'  => 'required|string',
            'instructions' => 'required|string',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $ingredientsArray = json_decode($validated['ingredients'], true);
        if (!is_array($ingredientsArray) || empty($ingredientsArray)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Format komposisi bahan tidak valid.'
            ], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            // Simpan ke storage/app/public/resep
            $imagePath = $request->file('image')->store('resep', 'public');
        }

        $recipe = Recipe::create([
            'title'        => $validated['title'],
            'image_url'    => $imagePath,
            'ingredients'  => $ingredientsArray,
            'instructions' => $validated['instructions'],
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Resep "' . $recipe->title . '" berhasil disimpan!',
            'data'    => $recipe
        ]);
    }

    public function updateResep(Request $request, $id)
    {
        $recipe = Recipe::findOrFail($id);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'ingredients'  => 'required|string',
            'instructions' => 'required|string',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $ingredientsArray = json_decode($validated['ingredients'], true);
        if (!is_array($ingredientsArray) || empty($ingredientsArray)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Format komposisi bahan tidak valid.'
            ], 422);
        }

        $imagePath = $recipe->image_url;
        if ($request->hasFile('image')) {
            if ($imagePath && !str_starts_with($imagePath, 'http') && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            // Simpan ke storage/app/public/resep
            $imagePath = $request->file('image')->store('resep', 'public');
        }

        $recipe->update([
            'title'        => $validated['title'],
            'image_url'    => $imagePath,
            'ingredients'  => $ingredientsArray,
            'instructions' => $validated['instructions'],
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Resep "' . $recipe->title . '" berhasil diperbarui!',
            'data'    => $recipe
        ]);
    }

    public function destroyResep($id)
    {
        $recipe = Recipe::findOrFail($id);

        if ($recipe->image_url && !str_starts_with($recipe->image_url, 'http') && Storage::disk('public')->exists($recipe->image_url)) {
            Storage::disk('public')->delete($recipe->image_url);
        }

        $recipe->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Resep digital berhasil dihapus dari arsip dapur.'
        ]);
    }
}