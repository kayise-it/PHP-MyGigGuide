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
        // #region agent log
        $logData = [
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'A',
            'location' => 'create_youtube_videos_table.php:up',
            'message' => 'Migration up() method called',
            'data' => [
                'database_name' => \DB::connection()->getDatabaseName(),
                'table_exists_before' => \Illuminate\Support\Facades\Schema::hasTable('youtube_videos'),
            ],
            'timestamp' => now()->timestamp * 1000,
        ];
        @file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
        // #endregion
        
        Schema::create('youtube_videos', function (Blueprint $table) {
            $table->id();
            $table->morphs('videoable'); // videoable_type, videoable_id
            $table->string('youtube_url');
            $table->string('youtube_video_id');
            $table->string('title')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            // Indexes for polymorphic relationship
            $table->index(['videoable_type', 'videoable_id']);
            $table->index('order');
        });
        
        // #region agent log
        $logData = [
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'A',
            'location' => 'create_youtube_videos_table.php:up',
            'message' => 'Migration up() completed - checking if table exists',
            'data' => [
                'table_exists_after' => \Illuminate\Support\Facades\Schema::hasTable('youtube_videos'),
            ],
            'timestamp' => now()->timestamp * 1000,
        ];
        @file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
        // #endregion
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('youtube_videos');
    }
};

