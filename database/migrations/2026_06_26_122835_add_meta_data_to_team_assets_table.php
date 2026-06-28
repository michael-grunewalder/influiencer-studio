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
        Schema::table('team_assets', function (Blueprint $table) {
            $table->json('meta_data')
                ->nullable()
                ->before('created_at')
                ->after('purpose');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_assets', function (Blueprint $table) {
            $table->dropColumn('meta_data');
        });
    }
};
