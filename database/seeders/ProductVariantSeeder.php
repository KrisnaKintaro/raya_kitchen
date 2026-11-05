<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductVariantSeeder extends Seeder
{
    public function run()
    {
        // 1. Produk Induk Manis
        $productIdManis = DB::table('products')->insertGetId([
            'name' => 'Roti Manis Spesial Raya',
            'description' => 'Roti super empuk fresh from the oven dengan berbagai pilihan isian manis yang lumer di mulut.',
            'is_active' => 1, 
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('product_variants')->insert([
            ['product_id' => $productIdManis, 'variant_name' => 'Coklat', 'image_url' => 'produk/roti1.jpeg', 'price' => 12000, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['product_id' => $productIdManis, 'variant_name' => 'Keju', 'image_url' => 'produk/roti1.jpeg', 'price' => 12000, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['product_id' => $productIdManis, 'variant_name' => 'Coklat Keju', 'image_url' => 'produk/roti1.jpeg', 'price' => 14000, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['product_id' => $productIdManis, 'variant_name' => 'Pisang Keju', 'image_url' => 'produk/roti1.jpeg', 'price' => 14000, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['product_id' => $productIdManis, 'variant_name' => 'Vanilla', 'image_url' => 'produk/roti1.jpeg', 'price' => 12000, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // 2. Produk Induk Gurih
        $productIdGurih = DB::table('products')->insertGetId([
            'name' => 'Roti Gurih & Savoury',
            'description' => 'Pilihan roti gurih dengan isian daging dan keju berkualitas, cocok untuk mengganjal perut.',
            'is_active' => 1, 
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('product_variants')->insert([
            ['product_id' => $productIdGurih, 'variant_name' => 'Pizza', 'image_url' => 'produk/roti1.jpeg', 'price' => 15000, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['product_id' => $productIdGurih, 'variant_name' => 'Smoke Beef', 'image_url' => 'produk/roti1.jpeg', 'price' => 16000, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}