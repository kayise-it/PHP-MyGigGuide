<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['artists', 'venues', 'organisers', 'events'] as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'claim_request_message')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->text('claim_request_message')->nullable()->after('dispute_reason');
            });
        }
    }

    public function down(): void
    {
        foreach (['artists', 'venues', 'organisers', 'events'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'claim_request_message')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('claim_request_message');
            });
        }
    }
};
