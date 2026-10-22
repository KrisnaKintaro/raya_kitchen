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
    Schema::create('company_profiles', function (Blueprint $table) {
        $table->id();
        $table->string('company_name');
        $table->year('established_year');
        $table->text('background_history');
        $table->integer('total_products_made')->default(0);
        $table->string('whatsapp_number');
        $table->string('email_address');
        $table->string('instagram_handle')->nullable();
        $table->string('image_url')->nullable(); // Kolom penampung nama file/URL gambar
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
