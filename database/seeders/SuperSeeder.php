<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\CompanyProfile;
use App\Models\StoreSetting;
use App\Models\StoreHoliday;
use App\Models\BotTemplate;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Recipe;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\UserNotification;

class SuperSeeder extends Seeder
{
    public function run(): void
    {
        // 1. NONAKTIFKAN FOREIGN KEY CHECKS BIAR BISA TRUNCATE BERSIH
        Schema::disableForeignKeyConstraints();

        $tables = [
            'users', 'user_addresses', 'company_profiles', 'store_settings', 'store_holidays',
            'bot_templates', 'products', 'product_variants', 'recipes', 'carts',
            'orders', 'order_items', 'transactions', 'user_notifications'
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        Schema::enableForeignKeyConstraints();

        // ==========================================
        // 2. SETTING TOKO & PROFIL
        // ==========================================
        CompanyProfile::create([
            'company_name' => 'Raya Kitchen',
            'established_year' => 2020,
            'background_history' => 'Berawal dari kecintaan terhadap pastry rumahan...',
            'short_description' => 'Bakery & Pastry Fresh Everyday.',
            'total_products_made' => 500,
            'whatsapp_number' => '6281234567890',
            'email_address' => 'hello@rayakitchen.com',
            'instagram_handle' => '@rayakitchen.id',
            'image_url' => 'default_profil.jpg',
            'address' => 'Jl. Husein Sastranegara Blok BE No. 26, Jatisari, Bekasi',
            'latitude' => -6.3262657,
            'longitude' => 106.9419131,
            'rajaongkir_destination_id' => 114, // ID Contoh Bekasi
        ]);

        StoreSetting::create([
            'max_qty_per_day' => 150,
            'max_orders_per_hour' => 10,
            'operational_time_start' => '08:00:00',
            'operational_time_end' => '17:00:00',
            'free_delivery_max_distance' => 10,
            'active_couriers' => 'jne,jnt,sicepat',
        ]);

        StoreHoliday::create([
            'holiday_date' => Carbon::tomorrow()->toDateString(),
            'description' => 'Libur Maintenance Dapur'
        ]);

        // ==========================================
        // 3. AKUN USERS (Semua Password: 123123)
        // ==========================================
        $password = Hash::make('123123');

        $admin = User::create([
            'name' => 'Admin Boss',
            'whatsapp_number' => '6289999999999',
            'password' => $password,
            'role' => 'admin',
        ]);

        $users = [];
        for ($i = 1; $i <= 3; $i++) {
            $users[] = User::create([
                'name' => 'User ' . $i,
                'whatsapp_number' => '628111111111' . $i,
                'password' => $password,
                'role' => 'pelanggan',
            ]);
        }

        // ==========================================
        // 4. ALAMAT PENGIRIMAN
        // ==========================================
        $address = UserAddress::create([
            'user_id' => $users[0]->id, // Alamat milik User 1
            'label' => 'Rumah',
            'recipient_name' => 'User 1',
            'recipient_whatsapp' => '6281111111111',
            'province' => 'Jawa Barat',
            'city' => 'Kota Bekasi',
            'district' => 'Jatiasih',
            'postal_code' => '17426',
            'street_address' => 'Jl. Mawar Merah No 5',
            'is_primary' => true,
            'distance_to_store' => 4.5,
            'rajaongkir_destination_id' => 114,
        ]);

        // ==========================================
        // 5. KATALOG PRODUK & VARIAN & RESEP
        // ==========================================
        $p1 = Product::create(['name' => 'Roti Manis', 'description' => 'Roti lembut aneka rasa manis', 'is_active' => true]);
        $p2 = Product::create(['name' => 'Roti Gurih', 'description' => 'Roti dengan isian daging dan keju', 'is_active' => true]);

        $v1 = ProductVariant::create(['product_id' => $p1->id, 'variant_name' => 'Coklat Lumer', 'price' => 12000, 'image_url' => 'default_roti.jpg']);
        $v2 = ProductVariant::create(['product_id' => $p1->id, 'variant_name' => 'Keju Susu', 'price' => 13000, 'image_url' => 'default_roti.jpg']);
        $v3 = ProductVariant::create(['product_id' => $p2->id, 'variant_name' => 'Abon Sapi', 'price' => 15000, 'image_url' => 'default_roti.jpg']);

        Recipe::create([
            'title' => 'Resep Roti Manis',
            'ingredients' => ['Tepung 1kg', 'Gula 200g', 'Ragi 10g'],
            'instructions' => 'Campur semua bahan lalu diamkan 1 jam. Panggang suhu 180C.',
        ]);

        // ==========================================
        // 6. KERANJANG (CART)
        // ==========================================
        // Kasih User 2 keranjang yang belum di checkout
        Cart::create(['user_id' => $users[1]->id, 'product_variant_id' => $v1->id, 'quantity' => 2]);
        Cart::create(['user_id' => $users[1]->id, 'product_variant_id' => $v3->id, 'quantity' => 1]);

        // ==========================================
        // 7. TRANSAKSI & PESANAN (SKENARIO LENGKAP)
        // ==========================================
        // Skenario 1: Pesanan LUNAS & SELESAI (Milik User 1)
        $order1 = Order::create([
            'user_id' => $users[0]->id,
            'order_number' => 'RK-' . date('Ymd') . '-0001',
            'recipient_name' => 'User 1',
            'recipient_whatsapp' => '6281111111111',
            'shipping_address' => 'Jl. Mawar Merah No 5, Bekasi',
            'delivery_date' => Carbon::today()->toDateString(),
            'delivery_time' => '10:00:00',
            'total_amount' => 24000, // 2x Varian 1
            'dp_amount' => 0,
            'remaining_amount' => 0,
            'status' => 'selesai',
            'shipping_cost' => 0,
            'courier_name' => 'Kurir Internal Raya Kitchen',
        ]);
        OrderItem::create([
            'order_id' => $order1->id, 'product_variant_id' => $v1->id,
            'quantity' => 2, 'price_at_order' => 12000, 'subtotal' => 24000, 'item_status' => 'selesai'
        ]);
        Transaction::create([
            'order_id' => $order1->id, 'amount' => 24000, 'payment_method' => 'transfer',
            'payment_status' => 'paid_full', 'paid_at' => Carbon::now()
        ]);
        UserNotification::create([
            'user_id' => $users[0]->id, 'order_number' => $order1->order_number,
            'title' => 'Pesanan Selesai', 'message' => 'Terima kasih telah berbelanja!', 'is_read' => true
        ]);

        // Skenario 2: Pesanan DP & DIPROSES (Milik User 1)
        $order2 = Order::create([
            'user_id' => $users[0]->id,
            'order_number' => 'RK-' . date('Ymd') . '-0002',
            'recipient_name' => 'User 1',
            'recipient_whatsapp' => '6281111111111',
            'shipping_address' => 'Jl. Mawar Merah No 5, Bekasi',
            'delivery_date' => Carbon::now()->addDays(2)->toDateString(),
            'delivery_time' => '15:00:00',
            'total_amount' => 45000, // 3x Varian 3
            'dp_amount' => 13500, // 30% dari 45k
            'remaining_amount' => 31500,
            'status' => 'diproses',
        ]);
        OrderItem::create([
            'order_id' => $order2->id, 'product_variant_id' => $v3->id,
            'quantity' => 3, 'price_at_order' => 15000, 'subtotal' => 45000, 'is_staged' => true, 'kitchen_priority' => 1, 'item_status' => 'diproses'
        ]);
        Transaction::create([
            'order_id' => $order2->id, 'amount' => 13500, 'payment_method' => 'transfer',
            'payment_status' => 'paid_dp', 'paid_at' => Carbon::now()
        ]);

        // Skenario 3: Pesanan DIBATALKAN (Milik User 3)
        $order3 = Order::create([
            'user_id' => $users[2]->id,
            'order_number' => 'RK-' . date('Ymd') . '-0003',
            'recipient_name' => 'User 3',
            'recipient_whatsapp' => '6281111111113',
            'shipping_address' => 'Ambil Sendiri',
            'delivery_date' => Carbon::today()->toDateString(),
            'delivery_time' => '12:00:00',
            'total_amount' => 13000,
            'status' => 'dibatalkan',
            'cancellation_reason' => 'Otomatis dibatalkan: Waktu pembayaran QRIS habis.',
        ]);
        OrderItem::create([
            'order_id' => $order3->id, 'product_variant_id' => $v2->id,
            'quantity' => 1, 'price_at_order' => 13000, 'subtotal' => 13000
        ]);
        Transaction::create([
            'order_id' => $order3->id, 'amount' => 13000, 'payment_method' => 'transfer',
            'payment_status' => 'failed'
        ]);

        // ==========================================
        // 8. TEMPLATE BOT WHATSAPP
        // ==========================================
        BotTemplate::create([
            'session_state' => 'sapaan',
            'description' => 'Sapaan Bot',
            'message_text' => 'Halo {nama}! Selamat datang di Super Bot Raya Kitchen.'
        ]);
    }
}
