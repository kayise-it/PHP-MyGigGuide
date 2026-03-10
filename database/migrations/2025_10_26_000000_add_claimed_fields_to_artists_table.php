<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('artists', function (Blueprint $table) {
            if (!Schema::hasColumn('artists', 'claimed_by_user_id')) {
                $table->unsignedBigInteger('claimed_by_user_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('artists', 'claimed_at')) {
                $table->timestamp('claimed_at')->nullable()->after('claimed_by_user_id');
            }
            if (!Schema::hasColumn('artists', 'claim_status')) {
                $table->enum('claim_status', ['unclaimed', 'pending', 'approved', 'rejected'])->default('unclaimed')->after('claimed_at');
            }
            if (!Schema::hasColumn('artists', 'claim_token')) {
                $table->string('claim_token', 64)->nullable()->unique()->after('claim_status');
            }
            if (!Schema::hasColumn('artists', 'claim_email')) {
                $table->string('claim_email')->nullable()->after('claim_token');
            }
        });
    }

    public function down()
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->dropColumn([
                'claimed_by_user_id',
                'claimed_at',
                'claim_status',
                'claim_token',
                'claim_email'
            ]);
        });
    }
};