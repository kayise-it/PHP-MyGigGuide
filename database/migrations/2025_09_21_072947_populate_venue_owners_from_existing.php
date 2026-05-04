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
        // Populate venue_owners table from existing venue user_id/owner_id
        // This migration ensures existing venues have at least one primary owner
        
        $venues = DB::table('venues')->get();
        
        foreach ($venues as $venue) {
            $userId = null;
            
            // First try user_id
            if ($venue->user_id) {
                $userId = $venue->user_id;
            } 
            // Then try owner_id if it's a User
            elseif ($venue->owner_id && $venue->owner_type === 'App\\Models\\User') {
                $userId = $venue->owner_id;
            }
            // If owner is Artist or Organiser, get their user_id
            elseif ($venue->owner_id && $venue->owner_type) {
                $ownerType = $venue->owner_type;
                if ($ownerType === 'App\\Models\\Artist' || $ownerType === 'App\\Models\\Organiser') {
                    $ownerTable = strtolower(class_basename($ownerType)) . 's';
                    $owner = DB::table($ownerTable)->where('id', $venue->owner_id)->first();
                    if ($owner && isset($owner->user_id)) {
                        $userId = $owner->user_id;
                    }
                }
            }
            
            // Only create entry if we found a valid user_id and it doesn't already exist
            if ($userId) {
                $exists = DB::table('venue_owners')
                    ->where('venue_id', $venue->id)
                    ->where('user_id', $userId)
                    ->exists();
                
                if (!$exists) {
                    DB::table('venue_owners')->insert([
                        'venue_id' => $venue->id,
                        'user_id' => $userId,
                        'role' => 'primary',
                        'added_by_user_id' => $userId,
                        'added_at' => $venue->created_at ?? now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration only populates data, so down() doesn't need to do anything
        // The actual table structure is managed by create_venue_owners_table migration
    }
};

