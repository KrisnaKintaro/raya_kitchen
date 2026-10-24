<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_holidays', function (Blueprint $table) {
            $table->id();
            $table->date('holiday_date')->unique(); // Tanggal liburnya
            $table->string('description')->nullable(); // Keterangan, misal "Libur Lebaran" atau "Dapur Renovasi"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_holidays');
    }
};
