<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Populating venue_owners table from existing venues...\n\n";

$venues = DB::table('venues')->get();
$count = 0;
$skipped = 0;
$errors = 0;

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
        if (strpos($ownerType, 'Artist') !== false) {
            $owner = DB::table('artists')->where('id', $venue->owner_id)->first();
            if ($owner && isset($owner->user_id)) {
                $userId = $owner->user_id;
            }
        } elseif (strpos($ownerType, 'Organiser') !== false) {
            $owner = DB::table('organisers')->where('id', $venue->owner_id)->first();
            if ($owner && isset($owner->user_id)) {
                $userId = $owner->user_id;
            }
        }
    }
    
    // Only create entry if we found a valid user_id and it doesn't already exist
    if ($userId) {
        // Verify user exists
        $userExists = DB::table('users')->where('id', $userId)->exists();
        if (!$userExists) {
            echo "⚠️  Venue #{$venue->id} ({$venue->name}): User ID {$userId} does not exist, skipping\n";
            $errors++;
            continue;
        }
        
        $exists = DB::table('venue_owners')
            ->where('venue_id', $venue->id)
            ->where('user_id', $userId)
            ->exists();
        
        if (!$exists) {
            try {
                DB::table('venue_owners')->insert([
                    'venue_id' => $venue->id,
                    'user_id' => $userId,
                    'role' => 'primary',
                    'added_by_user_id' => $userId,
                    'added_at' => $venue->created_at ?? now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $count++;
                echo "✅ Venue #{$venue->id} ({$venue->name}): Added user {$userId} as primary owner\n";
            } catch (Exception $e) {
                echo "❌ Venue #{$venue->id} ({$venue->name}): Error - {$e->getMessage()}\n";
                $errors++;
            }
        } else {
            $skipped++;
        }
    } else {
        echo "⚠️  Venue #{$venue->id} ({$venue->name}): No valid user_id found, skipping\n";
        $skipped++;
    }
}

echo "\n";
echo "✅ Complete!\n";
echo "   - Added: {$count} primary owners\n";
echo "   - Skipped: {$skipped} (already exist or no owner)\n";
echo "   - Errors: {$errors}\n";

