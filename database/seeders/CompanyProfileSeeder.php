<?php

namespace Database\Seeders;

use App\Models\CompanyProfile;
use Illuminate\Database\Seeder;

class CompanyProfileSeeder extends Seeder
{
    public function run(): void
    {
        CompanyProfile::create([
            'company_name' => 'Raya Kitchen',
            'established_year' => 2026,
            'background_history' => 'Berawal dari kecintaan terhadap pastry rumahan, Raya Kitchen didirikan pada tahun 2026. Kami bermula dari sebuah dapur kecil di Kediri yang berfokus pada kualitas bahan baku premium dan proses pemanggangan yang konsisten setiap paginya.',
            'total_products_made' => 150,
            'whatsapp_number' => '6281234567890',
            'instagram_handle' => '@rayakitchen.id',
            'image_url' => 'foto-tentang-kami.jpg',
             ]);
    }
}