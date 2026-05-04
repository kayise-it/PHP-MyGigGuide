<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('venue_owners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['primary', 'co_owner', 'manager'])->default('co_owner');
            $table->foreignId('added_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('added_at')->useCurrent();
            $table->timestamps();
            
            // Ensure unique user per venue
            $table->unique(['venue_id', 'user_id']);
            
            // Indexes for performance
            $table->index('venue_id');
            $table->index('user_id');
        });
        
        // Ensure only one primary owner per venue (using partial unique index)
        // Note: MySQL doesn't support partial indexes, so we'll enforce this in application logic
        // For PostgreSQL/SQLite, we could use: CREATE UNIQUE INDEX venue_owners_one_primary ON venue_owners (venue_id) WHERE role = 'primary';
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venue_owners');
    }
};

