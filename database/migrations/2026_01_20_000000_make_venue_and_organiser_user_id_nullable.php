<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Make venues.user_id nullable so venues can be marked unclaimed
        DB::statement('ALTER TABLE `venues` MODIFY `user_id` BIGINT UNSIGNED NULL');

        // Make organisers.user_id nullable so organiser profiles can be detached from users
        DB::statement('ALTER TABLE `organisers` MODIFY `user_id` BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     *
     * Note: this will fail if there are rows with NULL user_id, so it is mainly
     * provided for completeness and should be used with caution.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `venues` MODIFY `user_id` BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `organisers` MODIFY `user_id` BIGINT UNSIGNED NOT NULL');
    }
};

