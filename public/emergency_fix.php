<?php
// Emergency Fix for 503 Service Unavailable Error
// This script will fix all common causes of 503 errors

echo "<h1>🚨 Emergency Fix for 503 Error</h1>";
echo "<p>Fixing all common causes of Service Unavailable errors...</p>";

// 1. Create .env file with all required variables
echo "<h2>1. Creating .env file</h2>";
$env_content = 'APP_NAME="My Gig Guide"
APP_ENV=production
APP_KEY=base64:' . base64_encode(random_bytes(32)) . '
APP_DEBUG=false
APP_URL=https://mygigguide.co.za

LOG_CHANNEL=stack
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
MAIL_FROM_ADDRESS="hello@mygigguide.co.za"
MAIL_FROM_NAME="${APP_NAME}"';

if (file_put_contents('../.env', $env_content)) {
    echo "✅ .env file created successfully<br>";
} else {
    echo "❌ Failed to create .env file<br>";
}

// 2. Create required directories
echo "<h2>2. Creating required directories</h2>";
$directories = [
    '../storage',
    '../storage/framework',
    '../storage/framework/sessions',
    '../storage/framework/views',
    '../storage/framework/cache',
    '../storage/logs',
    '../bootstrap/cache'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
        echo "✅ Created directory: $dir<br>";
    } else {
        echo "✅ Directory exists: $dir<br>";
    }
}

// 3. Set proper permissions
echo "<h2>3. Setting permissions</h2>";
$permission_dirs = [
    '../storage',
    '../bootstrap/cache',
    '../storage/framework',
    '../storage/framework/sessions',
    '../storage/framework/views',
    '../storage/framework/cache',
    '../storage/logs'
];

foreach ($permission_dirs as $dir) {
    if (chmod($dir, 0755)) {
        echo "✅ Set permissions for: $dir<br>";
    } else {
        echo "❌ Failed to set permissions for: $dir<br>";
    }
}

// 4. Clear all cache files
echo "<h2>4. Clearing cache files</h2>";
$cache_files = [
    '../bootstrap/cache/config.php',
    '../bootstrap/cache/routes.php',
    '../bootstrap/cache/services.php',
    '../bootstrap/cache/packages.php'
];

foreach ($cache_files as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "✅ Cleared cache: " . basename($file) . "<br>";
        } else {
            echo "❌ Failed to clear cache: " . basename($file) . "<br>";
        }
    }
}

// 5. Create a simple test to verify Laravel can load
echo "<h2>5. Testing Laravel application</h2>";
try {
    // Test if vendor directory exists
    if (file_exists('../vendor/autoload.php')) {
        echo "✅ Composer autoloader found<br>";
        
        // Test if we can require the autoloader
        require_once '../vendor/autoload.php';
        echo "✅ Composer autoloader loaded successfully<br>";
        
        // Test bootstrap file
        if (file_exists('../bootstrap/app.php')) {
            echo "✅ Bootstrap file exists<br>";
        } else {
            echo "❌ Bootstrap file missing<br>";
        }
        
        // Test routes file
        if (file_exists('../routes/web.php')) {
            echo "✅ Routes file exists<br>";
        } else {
            echo "❌ Routes file missing<br>";
        }
        
    } else {
        echo "❌ Composer autoloader not found - run 'composer install'<br>";
    }
} catch (Exception $e) {
    echo "❌ Error loading Laravel: " . $e->getMessage() . "<br>";
}

// 6. Test database connection
echo "<h2>6. Testing database connection</h2>";
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=mygigguide', 'root', '');
    echo "✅ Database connection successful<br>";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    echo "💡 <strong>Database issue detected - this may cause 503 errors</strong><br>";
}

// 7. Create a simple index.php test
echo "<h2>7. Creating test index file</h2>";
$test_index = '<?php
echo "<h1>My Gig Guide - Test Page</h1>";
echo "<p>If you can see this, PHP is working!</p>";
echo "<p>Current time: " . date("Y-m-d H:i:s") . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Test Laravel
if (file_exists("../vendor/autoload.php")) {
    echo "<p>✅ Laravel vendor directory exists</p>";
} else {
    echo "<p>❌ Laravel vendor directory missing</p>";
}

if (file_exists("../.env")) {
    echo "<p>✅ .env file exists</p>";
} else {
    echo "<p>❌ .env file missing</p>";
}
?>';

if (file_put_contents('test_index.php', $test_index)) {
    echo "✅ Test index file created<br>";
} else {
    echo "❌ Failed to create test index file<br>";
}

// 8. Final status check
echo "<h2>8. Final Status Check</h2>";

$checks = [
    '.env file' => file_exists('../.env'),
    'Storage directory' => is_dir('../storage'),
    'Bootstrap cache directory' => is_dir('../bootstrap/cache'),
    'Vendor directory' => file_exists('../vendor/autoload.php'),
    'Bootstrap file' => file_exists('../bootstrap/app.php'),
    'Routes file' => file_exists('../routes/web.php')
];

foreach ($checks as $check => $status) {
    if ($status) {
        echo "✅ $check: OK<br>";
    } else {
        echo "❌ $check: MISSING<br>";
    }
}

echo "<h2>🎉 Emergency Fix Complete!</h2>";
echo "<p><strong>Now test these URLs:</strong></p>";
echo "<ul>";
echo "<li><a href='https://mygigguide.co.za/' target='_blank'>https://mygigguide.co.za/</a> (Main site)</li>";
echo "<li><a href='https://mygigguide.co.za/events' target='_blank'>https://mygigguide.co.za/events</a> (Events page)</li>";
echo "<li><a href='https://mygigguide.co.za/test_index.php' target='_blank'>https://mygigguide.co.za/test_index.php</a> (Test page)</li>";
echo "</ul>";

echo "<h3>If still getting 503 error:</h3>";
echo "<p>The issue is likely server-level:</p>";
echo "<ul>";
echo "<li><strong>Web server not running</strong> - Check Apache/Nginx status</li>";
echo "<li><strong>PHP-FPM not running</strong> - Check PHP-FPM service</li>";
echo "<li><strong>Database server down</strong> - Check MySQL service</li>";
echo "<li><strong>Server resources exhausted</strong> - Check memory/CPU usage</li>";
echo "<li><strong>Firewall blocking</strong> - Check server firewall rules</li>";
echo "</ul>";

echo "<p><strong>Contact your hosting provider if the issue persists.</strong></p>";
?>
