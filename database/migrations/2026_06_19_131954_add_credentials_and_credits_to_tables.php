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
        Schema::table('teams', function (Blueprint $table) {
            $table->string('fal_api_key')->nullable()->after('description');
            $table->decimal('credits', 8, 2)->default(0.00)->after('fal_api_key');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->decimal('credits', 8, 2)->default(0.00)->after('avatar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['fal_api_key', 'credits']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('credits');
        });
    }
};
