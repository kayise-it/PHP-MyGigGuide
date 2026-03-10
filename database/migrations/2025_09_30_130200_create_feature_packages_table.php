<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('feature_packages')) {
            Schema::create('feature_packages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('paid_feature_id')->constrained('paid_features')->cascadeOnDelete();
                $table->string('name');
                $table->unsignedInteger('duration_days');
                $table->unsignedBigInteger('price_cents');
                $table->string('currency', 3)->default('ZAR');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_packages');
    }
};


