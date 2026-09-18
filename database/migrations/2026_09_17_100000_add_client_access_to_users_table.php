<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * First-party access: which app skin, which platform, first seen, last access.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('first_seen_at')->nullable()->after('last_login_at');
            $table->timestamp('last_access_at')->nullable()->after('first_seen_at');
            $table->string('first_client', 32)->nullable()->after('last_access_at');
            $table->string('first_platform', 16)->nullable()->after('first_client');
            $table->string('last_client', 32)->nullable()->after('first_platform');
            $table->string('last_platform', 16)->nullable()->after('last_client');
        });

        DB::table('users')->whereNull('first_seen_at')->update([
            'first_seen_at' => DB::raw('COALESCE(last_login_at, created_at)'),
        ]);
        DB::table('users')->whereNull('last_access_at')->whereNotNull('last_login_at')->update([
            'last_access_at' => DB::raw('last_login_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_seen_at',
                'last_access_at',
                'first_client',
                'first_platform',
                'last_client',
                'last_platform',
            ]);
        });
    }
};
