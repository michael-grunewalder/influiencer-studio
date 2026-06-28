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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUlid('default_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('team_selection_mode')->default('default'); // 'default' or 'last_used'
            $table->foreignUlid('last_active_team_id')->nullable()->constrained('teams')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('default_team_id');
            $table->dropColumn('team_selection_mode');
            $table->dropConstrainedForeignId('last_active_team_id');
        });
    }
};
