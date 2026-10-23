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
                // Tanda * untuk perkalian sudah dibenarkan
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
                // Tanda * untuk perkalian sudah dibenarkan
                $itemSubtotal = $price * $item->quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'price_at_order' => $price,
                    'subtotal' => $itemSubtotal,
                ]);
            }

            \App\Models\Transaction::create([
                'order_id' => $order->id,
                'amount' => $totalAmount,
                'payment_method' => $request->input('payment_method', 'transfer'),
                'payment_status' => 'unpaid'
            ]);

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

        $order = Order::with(['items.productVariant', 'transaction']) 
            ->where('order_number', $orderNumber)
            ->first();

        if (!$order) {
            return redirect()->route('katalog')->with('error', 'Nota tidak ditemukan.');
        }

        return view('pembeli.pages.nota', compact('order'));
    }

    public function index(Request $request)
    {
        $userId = Auth::id() ?? 1;

        $orders = Order::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'order_number' => $order->order_number,
                    'status' => strtoupper(str_replace('_', ' ', $order->status)),
                    'date' => $order->created_at->format('d M Y, H:i'),
                    'recipient_name' => $order->recipient_name,
                    'recipient_whatsapp' => $order->recipient_whatsapp,
                    'shipping_address' => $order->shipping_address,
                    'total_amount_formatted' => 'Rp ' . number_format($order->total_amount, 0, ',', '.'),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }
}