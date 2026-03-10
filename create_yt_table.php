<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!Schema::hasTable('youtube_videos')) {
    Schema::create('youtube_videos', function (Blueprint $table) {
        $table->id();
        $table->morphs('videoable'); // videoable_type, videoable_id
        $table->string('youtube_url');
        $table->string('youtube_video_id');
        $table->string('title')->nullable();
        $table->integer('order')->default(0);
        $table->timestamps();

        // Indexes for polymorphic relationship
        // morphs() automatically adds an index, so adding another might cause an error
        // Let's wrap it in try-catch in case the index name is too long or duplicate
        try {
            $table->index(['videoable_type', 'videoable_id'], 'yt_videos_videoable_index');
        } catch (\Exception $e) {}
        
        try {
            $table->index('order');
        } catch (\Exception $e) {}
    });
    echo "youtube_videos table created successfully.\n";
} else {
    echo "youtube_videos table already exists.\n";
}
