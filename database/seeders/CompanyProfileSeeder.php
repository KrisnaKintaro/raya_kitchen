<?php

namespace Database\Seeders;

use App\Models\CompanyProfile;
use Illuminate\Database\Seeder;

class CompanyProfileSeeder extends Seeder
{
    public function run(): void
    {
        // Kosongkan data lama agar tidak dobel saat di-seed ulang
        CompanyProfile::truncate();

        CompanyProfile::create([
            'company_name' => 'Raya Kitchen',
            'established_year' => 2020,
            'background_history' => 'Berawal dari kecintaan terhadap pastry rumahan, Raya Kitchen didirikan pada tahun 2020. Kami bermula dari sebuah dapur kecil di Kediri yang berfokus pada kualitas bahan baku premium dan proses pemanggangan yang konsisten setiap paginya.',
            'short_description' => 'Menghadirkan roti dan kue kering fresh dari oven setiap harinya. Pesan lebih mudah, cepat, dan otomatis lewat integrasi sistem WhatsApp Bot kami.',
            'total_products_made' => 150,
            'whatsapp_number' => '6281234567890',
            'email_address' => 'hello@rayakitchen.com',
            'instagram_handle' => '@rayakitchen.id',
            'image_url' => 'foto-tentang-kami.jpg',
            'address' => 'Perum BDP, Jl. Husein Sastranegara Blok BE No. 26, Jatisari, Kec. Jatiasih, Kota Bekasi',
            'map_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3965.753896561214!2d106.9419131!3d-6.3262657!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwMTknMzQuNiJTIDEwNsKwNTYnMzAuOSJF!5e0!3m2!1sen!2sid!4v1690000000000!5m2!1sen!2sid',
            'map_external_url' => 'https://maps.google.com/?q=Perum+BDP,+Jl.+Husein+Sastranegara+Blok+BE+No.+26,+Jatisari,+Kec.+Jatiasih,+Kota+Bks',
        ]);
    }
}
