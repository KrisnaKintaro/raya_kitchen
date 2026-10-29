<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('refund_proof')->nullable()->after('refund_account_number');
            $table->enum('refund_status', ['none', 'pending', 'refunded'])->default('none')->after('refund_account_number');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('refund_proof');
            $table->dropColumn('refund_status');
        });
    }
};
