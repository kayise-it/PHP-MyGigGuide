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
        Schema::create('youtube_videos', function (Blueprint $table) {
            $table->id();
            // morphs() already adds an index on (videoable_type, videoable_id)
            $table->morphs('videoable');
            $table->string('youtube_url');
            $table->string('youtube_video_id');
            $table->string('title')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('youtube_videos');
    }
};

