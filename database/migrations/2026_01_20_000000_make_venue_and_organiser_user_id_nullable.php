<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            // Make venues.user_id nullable so venues can be marked unclaimed
            DB::statement('ALTER TABLE `venues` MODIFY `user_id` BIGINT UNSIGNED NULL');

            // Make organisers.user_id nullable so organiser profiles can be detached from users
            DB::statement('ALTER TABLE `organisers` MODIFY `user_id` BIGINT UNSIGNED NULL');

            return;
        }

        // SQLite / other drivers: use schema change syntax
        Schema::table('venues', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
        Schema::table('organisers', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Note: this will fail if there are rows with NULL user_id, so it is mainly
     * provided for completeness and should be used with caution.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `venues` MODIFY `user_id` BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE `organisers` MODIFY `user_id` BIGINT UNSIGNED NOT NULL');

            return;
        }

        // Note: this fails if NULL values exist.
        Schema::table('venues', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
        Schema::table('organisers', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};

