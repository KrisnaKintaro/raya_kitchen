<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number')->nullable()->after('shipping_service');
            }
            if (!Schema::hasColumn('orders', 'courier_status')) {
                // Status dari kurir eksternal: waiting_resi, in_transit, on_delivery, delivered, problem
                $table->string('courier_status')->default('waiting_resi')->after('tracking_number');
            }
            if (!Schema::hasColumn('orders', 'courier_manifest')) {
                $table->json('courier_manifest')->nullable()->after('courier_status');
            }
            if (!Schema::hasColumn('orders', 'arrival_proof')) {
                $table->string('arrival_proof')->nullable()->after('delivery_proof');
            }
            if (!Schema::hasColumn('orders', 'payment_proof')) {
                $table->string('payment_proof')->nullable()->after('arrival_proof');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $cols = collect(['tracking_number', 'courier_status', 'courier_manifest', 'arrival_proof', 'payment_proof'])
                ->filter(fn ($col) => Schema::hasColumn('orders', $col))
                ->all();

            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
