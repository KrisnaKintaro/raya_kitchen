<?php

namespace App\Http\Controllers\Api\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id() ?? 1;
        
        $carts = Cart::with('productVariant.product')
            ->where('user_id', $userId)
            ->get()
            ->map(function ($cart) {
                return [
                    'id' => $cart->id,
                    'name' => $cart->productVariant->variant_name ?? 'Produk Roti',
                    'price' => $cart->productVariant->price ?? 0,
                    'qty' => $cart->quantity,
                    'image' => $cart->productVariant->image_url ?? 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=300&q=80'
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $carts
            ]
        ], 200);
    }

    public function update(Request $request)
    {
        $cart = Cart::find($request->item_id);
        if ($cart) {
            $cart->quantity = $request->qty;
            $cart->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Quantity berhasil diupdate'
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Item tidak ditemukan'], 404);
    }

    public function destroy(Request $request)
    {
        $cart = Cart::find($request->item_id);
        if ($cart) {
            $cart->delete();
            return response()->json(['status' => 'success', 'message' => 'Item berhasil dihapus']);
        }

        return response()->json(['status' => 'error', 'message' => 'Item tidak ditemukan'], 404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required',
            'quantity' => 'required|integer|min:1'
        ]);

        $userId = Auth::id() ?? 1; 

        // Cek apakah item sudah ada di keranjang
        $cart = Cart::where('user_id', $userId)
            ->where('product_variant_id', $request->product_variant_id)
            ->first();

        if ($cart) {
            $cart->quantity += $request->quantity;
            $cart->save();
        } else {
            Cart::create([
                'user_id' => $userId,
                'product_variant_id' => $request->product_variant_id,
                'quantity' => $request->quantity,
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Berhasil masuk keranjang!']);
    }
}