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
        // Ensure a clean state in case a partially-created table already exists
        Schema::dropIfExists('media');

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['profile', 'gallery', 'qr'])->default('gallery');
            $table->string('path');
            $table->string('disk')->default('public');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->bigInteger('size_bytes')->nullable();
            $table->string('checksum_sha1', 40)->nullable();
            $table->integer('order_index')->nullable();
            $table->timestamps();

            $table->index(['artist_id', 'type']);
            $table->unique(['artist_id', 'path']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};


