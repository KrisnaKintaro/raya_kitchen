<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('label')->nullable(); // Label alamat (Rumah, Kantor, dll)
            $table->string('recipient_name'); // Nama penerima
            $table->string('recipient_whatsapp'); // No WA penerima (bisa beda dengan nomor akun)

            // Pembagian wilayah
            $table->string('province'); // Provinsi
            $table->string('city'); // Kota / Kabupaten
            $table->string('district'); // Kecamatan
            $table->string('postal_code'); // Kode Pos

            // Detail Jalan & Patokan
            $table->string('street_address'); // Nama jalan/gedung/no rumah
            $table->text('landmark')->nullable(); // Patokan, warna cat, dll (opsional)

            $table->boolean('is_primary')->default(false); // Penanda alamat utama
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
