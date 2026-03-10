<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feature_packages', function (Blueprint $table) {
            if (! Schema::hasColumn('feature_packages', 'paid_feature_id')) {
                $table->foreignId('paid_feature_id')->nullable()->after('id')->constrained('paid_features')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('feature_packages', function (Blueprint $table) {
            if (Schema::hasColumn('feature_packages', 'paid_feature_id')) {
                $table->dropConstrainedForeignId('paid_feature_id');
            }
        });
    }
};









