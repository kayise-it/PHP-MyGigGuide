<?php
// Comprehensive 503 Service Unavailable Fix
// Access this file directly: https://mygigguide.co.za/fix_503.php

echo "<h1>🔧 My Gig Guide - 503 Error Fix</h1>";

// Step 1: Create .env file
echo "<h2>Step 1: Creating .env file</h2>";

$env_content = 'APP_NAME="My Gig Guide"
APP_ENV=production
APP_KEY=base64:your-app-key-here
APP_DEBUG=false
APP_URL=https://mygigguide.co.za

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mygigguide
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"';

if (file_put_contents('../.env', $env_content)) {
    echo "✅ .env file created successfully<br>";
} else {
    echo "❌ Failed to create .env file<br>";
}

// Step 2: Set proper permissions
echo "<h2>Step 2: Setting permissions</h2>";

$directories = [
    '../storage',
    '../bootstrap/cache',
    '../storage/framework',
    '../storage/framework/sessions',
    '../storage/framework/views',
    '../storage/framework/cache',
    '../storage/logs'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
        echo "✅ Created directory: $dir<br>";
    }
    chmod($dir, 0755);
    echo "✅ Set permissions for: $dir<br>";
}

// Step 3: Generate application key
echo "<h2>Step 3: Generating application key</h2>";

$key = 'base64:' . base64_encode(random_bytes(32));
$env_content = str_replace('APP_KEY=base64:your-app-key-here', 'APP_KEY=' . $key, $env_content);
file_put_contents('../.env', $env_content);
echo "✅ Application key generated: " . substr($key, 0, 20) . "...<br>";

// Step 4: Clear caches
echo "<h2>Step 4: Clearing caches</h2>";

$cache_files = [
    '../bootstrap/cache/config.php',
    '../bootstrap/cache/routes.php',
    '../bootstrap/cache/services.php',
    '../bootstrap/cache/packages.php'
];

foreach ($cache_files as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "✅ Cleared cache: $file<br>";
    }
}

// Step 5: Test Laravel application
echo "<h2>Step 5: Testing Laravel application</h2>";

try {
    // Test if we can load Laravel
    if (file_exists('../vendor/autoload.php')) {
        require_once '../vendor/autoload.php';
        echo "✅ Composer autoloader loaded<br>";
        
        // Test bootstrap
        if (file_exists('../bootstrap/app.php')) {
            echo "✅ Bootstrap file exists<br>";
        } else {
            echo "❌ Bootstrap file missing<br>";
        }
        
        // Test routes
        if (file_exists('../routes/web.php')) {
            echo "✅ Routes file exists<br>";
        } else {
            echo "❌ Routes file missing<br>";
        }
        
    } else {
        echo "❌ Composer autoloader not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Error loading Laravel: " . $e->getMessage() . "<br>";
}

// Step 6: Database test
echo "<h2>Step 6: Database connection test</h2>";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=mygigguide', 'root', '');
    echo "✅ Database connection successful<br>";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// Step 7: Final status
echo "<h2>Step 7: Final Status</h2>";

if (file_exists('../.env')) {
    echo "✅ .env file exists<br>";
} else {
    echo "❌ .env file missing<br>";
}

if (is_writable('../storage')) {
    echo "✅ Storage directory is writable<br>";
} else {
    echo "❌ Storage directory is not writable<br>";
}

if (is_writable('../bootstrap/cache')) {
    echo "✅ Bootstrap cache directory is writable<br>";
} else {
    echo "❌ Bootstrap cache directory is not writable<br>";
}

echo "<h2>🎉 Fix Complete!</h2>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>Try accessing: <a href='https://mygigguide.co.za/'>https://mygigguide.co.za/</a></li>";
echo "<li>Try accessing: <a href='https://mygigguide.co.za/events'>https://mygigguide.co.za/events</a></li>";
echo "<li>If still having issues, check the diagnostic: <a href='https://mygigguide.co.za/diagnostic.php'>https://mygigguide.co.za/diagnostic.php</a></li>";
echo "</ol>";

echo "<p><strong>If the 503 error persists, the issue may be:</strong></p>";
echo "<ul>";
echo "<li>Web server configuration (Apache/Nginx)</li>";
echo "<li>PHP-FPM not running</li>";
echo "<li>Database server not running</li>";
echo "<li>Insufficient server resources</li>";
echo "</ul>";
?>
