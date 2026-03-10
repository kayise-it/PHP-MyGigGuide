<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feature_programs', function (Blueprint $table) {
            if (! Schema::hasColumn('feature_programs', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('feature_programs', function (Blueprint $table) {
            if (Schema::hasColumn('feature_programs', 'slug')) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            }
        });
    }
};









