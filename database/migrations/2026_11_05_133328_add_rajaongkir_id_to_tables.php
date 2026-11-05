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
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->integer('rajaongkir_destination_id')->nullable()->after('longitude');
        });
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->integer('rajaongkir_destination_id')->nullable()->after('distance_to_store');
        });
    }

    public function down(): void {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn('rajaongkir_destination_id');
        });
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn('rajaongkir_destination_id');
        });
    }
};
