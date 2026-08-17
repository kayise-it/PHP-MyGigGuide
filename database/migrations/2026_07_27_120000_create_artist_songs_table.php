<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artist_songs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('original_artist')->nullable();
            $table->boolean('is_original')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('reference_youtube_id', 20)->nullable();
            $table->string('reference_spotify_id', 64)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['artist_id', 'sort_order']);
            $table->index(['artist_id', 'title']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_songs');
    }
};
