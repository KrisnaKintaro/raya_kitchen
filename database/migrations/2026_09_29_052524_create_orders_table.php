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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->string('recipient_name');
            $table->string('recipient_whatsapp');
            $table->text('shipping_address')->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['pending', 'diproses', 'siap_diambil', 'selesai', 'dibatalkan'])->default('pending');
            $table->enum('order_source', ['web', 'whatsapp'])->default('web');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
