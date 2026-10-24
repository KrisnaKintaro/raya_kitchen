<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->text('short_description')->nullable()->after('background_history');
            $table->text('address')->nullable()->after('whatsapp_number');
            $table->text('map_embed_url')->nullable()->after('address');
            $table->text('map_external_url')->nullable()->after('map_embed_url');
        });
    }

    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn(['short_description', 'address', 'map_embed_url', 'map_external_url']);
        });
    }
};
