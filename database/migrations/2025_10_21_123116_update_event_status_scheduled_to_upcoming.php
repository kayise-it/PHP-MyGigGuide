<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all events with status 'scheduled' to 'upcoming'
        DB::table('events')
            ->where('status', 'scheduled')
            ->update(['status' => 'upcoming']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert all events with status 'upcoming' back to 'scheduled'
        DB::table('events')
            ->where('status', 'upcoming')
            ->update(['status' => 'scheduled']);
    }
};
