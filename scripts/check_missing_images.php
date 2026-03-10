<?php

/**
 * Script to check which venue images are missing from the server
 * Run: php scripts/check_missing_images.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Venue;
use Illuminate\Support\Facades\Storage;

echo "==========================================\n";
echo "Checking Missing Venue Images\n";
echo "==========================================\n\n";

$venues = Venue::whereNotNull('main_picture')->get();
$total = $venues->count();
$missing = 0;
$found = 0;
$missingPaths = [];

echo "Checking {$total} venues with images...\n\n";

foreach ($venues as $venue) {
    $imagePath = $venue->main_picture;
    
    if (Storage::disk('public')->exists($imagePath)) {
        $found++;
        echo "✓ {$venue->name}: {$imagePath}\n";
    } else {
        $missing++;
        $missingPaths[] = [
            'venue_id' => $venue->id,
            'venue_name' => $venue->name,
            'image_path' => $imagePath,
            'folder' => dirname($imagePath)
        ];
        echo "✗ {$venue->name}: {$imagePath} - MISSING\n";
    }
}

echo "\n==========================================\n";
echo "Summary\n";
echo "==========================================\n";
echo "Total venues with images: {$total}\n";
echo "Images found: {$found}\n";
echo "Images missing: {$missing}\n\n";

if ($missing > 0) {
    echo "Missing images breakdown by folder:\n";
    $folderGroups = [];
    foreach ($missingPaths as $item) {
        $folder = $item['folder'];
        if (!isset($folderGroups[$folder])) {
            $folderGroups[$folder] = 0;
        }
        $folderGroups[$folder]++;
    }
    
    arsort($folderGroups);
    foreach ($folderGroups as $folder => $count) {
        echo "  {$folder}: {$count} images\n";
    }
    
    // Save missing paths to file
    $reportFile = storage_path('app/missing_venue_images.json');
    file_put_contents($reportFile, json_encode($missingPaths, JSON_PRETTY_PRINT));
    echo "\nMissing image paths saved to: {$reportFile}\n";
}

echo "\n==========================================\n";
echo "Next Steps:\n";
echo "==========================================\n";
echo "1. Download images from remote server\n";
echo "2. Place them in: storage/app/public/\n";
echo "3. Run this script again to verify\n";
echo "\n";

