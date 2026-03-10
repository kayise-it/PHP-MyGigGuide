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
        Schema::create('feature_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('paid_feature_id')->constrained('paid_features')->cascadeOnDelete();
            // Polymorphic: which entity is being boosted
            $table->unsignedBigInteger('featureable_id');
            $table->string('featureable_type');
            $table->unsignedBigInteger('price_cents_at_purchase');
            $table->string('currency', 3)->default('ZAR');
            $table->enum('status', ['pending', 'paid', 'active', 'expired', 'failed', 'refunded'])->default('pending');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('payment_meta')->nullable();
            $table->timestamps();

            $table->index(['featureable_type', 'featureable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_purchases');
    }
};









