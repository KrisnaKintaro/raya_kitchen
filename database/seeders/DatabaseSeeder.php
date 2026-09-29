<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        User::factory()->create([
            'name' => 'Admin Raya Kitchen',
            'whatsapp_number' => '6281234567890', // Ganti email jadi nomor WA
            'role' => 'admin',
            'password' => bcrypt('password123')
        ]);
    }
}
