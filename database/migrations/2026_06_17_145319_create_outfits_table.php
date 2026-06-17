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
        Schema::create('outfits', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('influencer_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('top')->nullable();
            $table->string('bottom')->nullable();
            $table->string('hairstyle')->nullable();
            $table->text('full_look_description')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outfits');
    }
};
