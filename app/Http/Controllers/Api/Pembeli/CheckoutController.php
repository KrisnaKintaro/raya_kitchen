<?php

namespace App\Http\Controllers\Api\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $userId = Auth::id() ?? 1;

        $cartItems = Cart::with('productVariant')->where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Keranjang belanja masih kosong!'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item->productVariant->price * $item->quantity;
            }
            // Tambah ongkir
            $totalAmount += 10000;

            $orderNumber = 'RK-' . date('Ymd') . '-' . rand(1000, 9999);

            // Simpan ke tabel orders
            $order = Order::create([
                'user_id' => $userId,
                'order_number' => $orderNumber,
                'recipient_name' => $request->input('name'),
                'recipient_whatsapp' => $request->input('phone'),
                'shipping_address' => $request->input('address'),
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'order_source' => 'web',
            ]);

            // Simpan ke tabel order_items
            foreach ($cartItems as $item) {
                $price = $item->productVariant->price;
                $itemSubtotal = $price * $item->quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'price_at_order' => $price,
                    'subtotal' => $itemSubtotal,
                ]);
            }

            // Kosongkan keranjang setelah order berhasil
            Cart::where('user_id', $userId)->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil dibuat!',
                'data' => [
                    'order_number' => $orderNumber,
                    'redirect_url' => url('/nota?order_number=' . $orderNumber)
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method untuk menampilkan Nota dari database
    public function showNota(Request $request)
    {
        $orderNumber = $request->query('order_number');

        $order = Order::with('items.productVariant')
            ->where('order_number', $orderNumber)
            ->first();

        if (!$order) {
            return redirect()->route('katalog')->with('error', 'Nota tidak ditemukan.');
        }

        return view('pembeli.pages.nota', compact('order'));
    }
}