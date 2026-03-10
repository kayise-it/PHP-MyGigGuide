<?php
/**
 * Manual cache clearing script
 * Use this when 'php artisan config:clear' fails due to PHP version issues
 * 
 * Usage: php clear_cache.php
 */

$root = __DIR__;

echo "Clearing Laravel caches...\n\n";

// Cache files to remove
$cacheFiles = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes.php',
    'bootstrap/cache/services.php',
    'bootstrap/cache/packages.php',
];

$cleared = 0;
foreach ($cacheFiles as $file) {
    $path = $root . '/' . $file;
    if (file_exists($path)) {
        if (unlink($path)) {
            echo "✓ Cleared: $file\n";
            $cleared++;
        } else {
            echo "✗ Failed to clear: $file\n";
        }
    } else {
        echo "- Not found: $file\n";
    }
}

// Clear framework cache directories
$cacheDirs = [
    'storage/framework/cache/data',
    'storage/framework/views',
    'storage/framework/sessions',
];

foreach ($cacheDirs as $dir) {
    $path = $root . '/' . $dir;
    if (is_dir($path)) {
        $files = glob($path . '/*');
        $count = 0;
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $count++;
            }
        }
        if ($count > 0) {
            echo "✓ Cleared $count files from: $dir\n";
        }
    }
}

echo "\n✅ Cache clearing complete!\n";
echo "Cleared $cleared cache files.\n";
echo "\nNote: Your Facebook App Secret changes will now be active.\n";




