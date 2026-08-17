<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->string('context', 64)->index();
            $table->string('question');
            $table->json('options');
            $table->boolean('is_active')->default(true);
            $table->timestamp('closes_at')->nullable();
            $table->timestamps();
        });

        Schema::create('poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
            $table->string('device_id', 128);
            $table->unsignedTinyInteger('option_index');
            $table->timestamps();

            $table->unique(['poll_id', 'device_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_votes');
        Schema::dropIfExists('polls');
    }
};
