<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->foreignId('pending_claim_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamp('pending_claim_at')->nullable();
            $table->boolean('dispute_raised')->default(false);
            $table->timestamp('dispute_raised_at')->nullable();
            $table->enum('claim_status', ['none', 'pending', 'approved', 'disputed', 'rejected'])->default('none');
            $table->timestamp('grace_period_ends_at')->nullable();
            $table->timestamp('warning_email_sent_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->dropForeign(['pending_claim_user_id']);
            $table->dropColumn([
                'pending_claim_user_id',
                'pending_claim_at',
                'dispute_raised',
                'dispute_raised_at',
                'claim_status',
                'grace_period_ends_at',
                'warning_email_sent_at',
            ]);
        });
    }
};
