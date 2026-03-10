<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paid_features', function (Blueprint $table) {
            $table->foreignId('feature_program_id')->nullable()->after('id')->constrained('feature_programs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('paid_features', function (Blueprint $table) {
            $table->dropConstrainedForeignId('feature_program_id');
        });
    }
};









