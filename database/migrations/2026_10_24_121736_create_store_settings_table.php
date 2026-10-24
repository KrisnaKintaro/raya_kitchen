<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            // Kuota Harian (60 pcs)
            $table->integer('max_qty_per_day')->default(60);

            // Kuota Jam Pengiriman/Pengambilan
            $table->integer('max_orders_per_hour')->default(5);

            // Jam Operasional (Biar gampang diedit)
            $table->time('operational_time_start')->default('08:00:00');
            $table->time('operational_time_end')->default('17:00:00');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
