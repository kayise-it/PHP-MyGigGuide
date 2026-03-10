<?php
/**
 * Create venue owner tables directly
 * Visit: https://www.mygigguide.co.za/setup-venue-tables.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

header('Content-Type: text/html; charset=utf-8');

try {
    $db = DB::connection();
    $pdo = $db->getPdo();
    
    echo "<h1>Creating Venue Owner Tables</h1>";
    echo "<pre style='background: #f5f5f5; padding: 20px; border-radius: 8px;'>";
    
    // Check if tables already exist
    $tables = ['venue_owners', 'venue_owner_requests'];
    $existing = [];
    foreach ($tables as $table) {
        try {
            $result = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($result->rowCount() > 0) {
                $existing[] = $table;
                echo "✅ Table '$table' already exists\n";
            }
        } catch (Exception $e) {
            // Table doesn't exist, continue
        }
    }
    
    // Create venue_owners table if it doesn't exist
    if (!in_array('venue_owners', $existing)) {
        echo "\n--- Creating venue_owners table ---\n";
        $sql = "CREATE TABLE IF NOT EXISTS `venue_owners` (
          `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          `venue_id` bigint(20) UNSIGNED NOT NULL,
          `user_id` bigint(20) UNSIGNED NOT NULL,
          `role` enum('primary','co_owner','manager') NOT NULL DEFAULT 'co_owner',
          `added_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
          `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `venue_owners_venue_id_user_id_unique` (`venue_id`,`user_id`),
          KEY `venue_owners_venue_id_index` (`venue_id`),
          KEY `venue_owners_user_id_index` (`user_id`),
          KEY `venue_owners_added_by_user_id_foreign` (`added_by_user_id`),
          CONSTRAINT `venue_owners_venue_id_foreign` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE CASCADE,
          CONSTRAINT `venue_owners_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
          CONSTRAINT `venue_owners_added_by_user_id_foreign` FOREIGN KEY (`added_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        try {
            $pdo->exec($sql);
            echo "✅ Created venue_owners table\n";
        } catch (PDOException $e) {
            echo "❌ Error creating venue_owners: " . $e->getMessage() . "\n";
        }
    }
    
    // Create venue_owner_requests table if it doesn't exist
    if (!in_array('venue_owner_requests', $existing)) {
        echo "\n--- Creating venue_owner_requests table ---\n";
        $sql = "CREATE TABLE IF NOT EXISTS `venue_owner_requests` (
          `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          `venue_id` bigint(20) UNSIGNED NOT NULL,
          `requester_user_id` bigint(20) UNSIGNED NOT NULL,
          `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
          `reason` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `proof_document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `reviewed_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
          `reviewed_at` timestamp NULL DEFAULT NULL,
          `rejection_reason` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `unique_venue_requester` (`venue_id`,`requester_user_id`),
          KEY `venue_owner_requests_venue_id_index` (`venue_id`),
          KEY `venue_owner_requests_requester_user_id_index` (`requester_user_id`),
          KEY `venue_owner_requests_status_index` (`status`),
          KEY `venue_owner_requests_reviewed_by_user_id_foreign` (`reviewed_by_user_id`),
          CONSTRAINT `venue_owner_requests_venue_id_foreign` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE CASCADE,
          CONSTRAINT `venue_owner_requests_requester_user_id_foreign` FOREIGN KEY (`requester_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
          CONSTRAINT `venue_owner_requests_reviewed_by_user_id_foreign` FOREIGN KEY (`reviewed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        try {
            $pdo->exec($sql);
            echo "✅ Created venue_owner_requests table\n";
        } catch (PDOException $e) {
            echo "❌ Error creating venue_owner_requests: " . $e->getMessage() . "\n";
        }
    }
    
    // Verify tables exist
    echo "\n--- Verifying Tables ---\n";
    foreach ($tables as $table) {
        try {
            $result = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($result->rowCount() > 0) {
                echo "✅ Table '$table' exists\n";
            } else {
                echo "❌ Table '$table' does NOT exist\n";
            }
        } catch (PDOException $e) {
            echo "❌ Error checking table '$table': " . $e->getMessage() . "\n";
        }
    }
    
    // Populate existing venues with primary owners
    echo "\n--- Populating Existing Venues ---\n";
    try {
        $venues = DB::table('venues')->get();
        $count = 0;
        $skipped = 0;
        foreach ($venues as $venue) {
            $userId = null;
            
            if ($venue->user_id) {
                $userId = $venue->user_id;
            } elseif ($venue->owner_id && $venue->owner_type === 'App\\Models\\User') {
                $userId = $venue->owner_id;
            } elseif ($venue->owner_id && $venue->owner_type) {
                // Try to get user_id from owner model
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
                    $count++;
                } else {
                    $skipped++;
                }
            }
        }
        echo "✅ Populated $count venues with primary owners\n";
        if ($skipped > 0) {
            echo "ℹ️  Skipped $skipped venues (already have owners)\n";
        }
    } catch (Exception $e) {
        echo "⚠️  Error populating venues: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ Setup complete! The Claim Venue feature should now work.\n";
    echo "</pre>";
    echo "<p><a href='/dashboard' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background: #7c3aed; color: white; text-decoration: none; border-radius: 8px;'>Go to Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error</h2>";
    echo "<pre style='background: #fee; padding: 20px; border-radius: 8px; color: #c00;'>";
    echo $e->getMessage() . "\n\n";
    echo $e->getTraceAsString();
    echo "</pre>";
}

