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
        Schema::create('paid_features', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('applies_to', ['artist', 'venue', 'event']);
            $table->text('description')->nullable();
            $table->unsignedInteger('duration_days')->default(7);
            $table->unsignedBigInteger('price_cents');
            $table->string('currency', 3)->default('ZAR');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paid_features');
    }
};









