<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('order_items', function (Blueprint $table) {
            // Penanda apakah item ini udah masuk antrean masak
            $table->boolean('is_staged')->default(false)->after('subtotal');
            // Urutan prioritas masak di kolom Staging
            $table->integer('kitchen_priority')->default(0)->after('is_staged');
            // Status spesifik per item roti
            $table->enum('item_status', ['pool', 'staging', 'diproses', 'selesai'])->default('pool')->after('kitchen_priority');
        });
    }

    public function down(): void {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['is_staged', 'kitchen_priority', 'item_status']);
        });
    }
};
