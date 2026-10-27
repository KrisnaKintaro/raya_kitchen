<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class StoreSettingSeeder extends Seeder
{
    public function run(): void
    {
        StoreSetting::create([
            'max_qty_per_day' => 130,
            'max_orders_per_hour' => 5,
            'operational_time_start' => '08:00:00',
            'operational_time_end' => '17:00:00',
        ]);
    }
}
