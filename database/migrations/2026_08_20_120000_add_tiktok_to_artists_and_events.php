<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->string('tiktok')->nullable()->after('twitter');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('tiktok')->nullable()->after('ticket_url');
        });
    }

    public function down(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->dropColumn('tiktok');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('tiktok');
        });
    }
};
