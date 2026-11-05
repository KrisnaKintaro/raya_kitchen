<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('shipping_cost', 12, 2)->default(0)->after('delivery_time');
            $table->string('courier_name')->nullable()->after('shipping_cost');
            $table->string('shipping_service')->nullable()->after('courier_name');
        });
    }

    public function down(): void {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_cost', 'courier_name', 'shipping_service']);
        });
    }
};
