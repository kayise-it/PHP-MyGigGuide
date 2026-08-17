<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('song_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artist_song_id')->nullable()->constrained('artist_songs')->nullOnDelete();
            $table->string('message', 500)->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('tip_reference', 100)->nullable();
            $table->timestamps();

            $table->index(['live_session_id', 'status']);
            $table->index(['live_session_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('song_requests');
    }
};
