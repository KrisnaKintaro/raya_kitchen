<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('free_delivery_max_distance')->nullable();
            $table->string('active_couriers')->nullable()->default('pemilik_toko,jne,sicepat,jnt')->after('free_delivery_max_distance');
        });
    }

    public function down(): void {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn('active_couriers');
        });
    }
};
