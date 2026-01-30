<?php
/**
 * Quick script to create venue owner tables
 * Visit: https://www.mygigguide.co.za/create-tables.php
 * Remove this file after tables are created for security
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $db = DB::connection();
    $pdo = $db->getPdo();
    
    echo "<h1>Creating Venue Owner Tables</h1>";
    echo "<pre>";
    
    // Read SQL file
    $sqlFile = __DIR__ . '/../create_venue_owner_tables.sql';
    if (!file_exists($sqlFile)) {
        die("SQL file not found: $sqlFile\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            echo "✅ Executed: " . substr($statement, 0, 50) . "...\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "ℹ️  Table already exists (skipping)\n";
            } else {
                echo "❌ Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Verify tables exist
    echo "\n--- Verifying Tables ---\n";
    $tables = ['venue_owners', 'venue_owner_requests'];
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
        foreach ($venues as $venue) {
            $userId = null;
            
            if ($venue->user_id) {
                $userId = $venue->user_id;
            } elseif ($venue->owner_id && $venue->owner_type === 'App\\Models\\User') {
                $userId = $venue->owner_id;
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
                }
            }
        }
        echo "✅ Populated $count venues with primary owners\n";
    } catch (Exception $e) {
        echo "⚠️  Error populating venues: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ Done! You can now use the Claim Venue feature.\n";
    echo "</pre>";
    echo "<p><a href='/dashboard'>Go to Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<pre>" . $e->getMessage() . "\n\n" . $e->getTraceAsString() . "</pre>";
}

