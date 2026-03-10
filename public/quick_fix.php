<?php
// Quick Fix for 503 Service Unavailable Error
// This will create the missing .env file and fix permissions

echo "<h1>🚀 Quick Fix for 503 Error</h1>";

// Create .env file with minimal required configuration
$env_content = 'APP_NAME="My Gig Guide"
APP_ENV=production
APP_KEY=base64:' . base64_encode(random_bytes(32)) . '
APP_DEBUG=false
APP_URL=https://mygigguide.co.za

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mygigguide
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync';

// Write .env file
if (file_put_contents('../.env', $env_content)) {
    echo "✅ .env file created successfully<br>";
} else {
    echo "❌ Failed to create .env file<br>";
}

// Set permissions
chmod('../.env', 0644);
chmod('../storage', 0755);
chmod('../bootstrap/cache', 0755);

// Clear cache files
$cache_files = [
    '../bootstrap/cache/config.php',
    '../bootstrap/cache/routes.php',
    '../bootstrap/cache/services.php'
];

foreach ($cache_files as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "✅ Cleared cache: " . basename($file) . "<br>";
    }
}

echo "<h2>✅ Fix Applied!</h2>";
echo "<p><strong>Now try accessing:</strong></p>";
echo "<ul>";
echo "<li><a href='https://mygigguide.co.za/' target='_blank'>https://mygigguide.co.za/</a></li>";
echo "<li><a href='https://mygigguide.co.za/events' target='_blank'>https://mygigguide.co.za/events</a></li>";
echo "</ul>";

echo "<p><strong>If still getting 503 error, the issue might be:</strong></p>";
echo "<ul>";
echo "<li>Web server (Apache/Nginx) not running</li>";
echo "<li>PHP-FPM not running</li>";
echo "<li>Database server not running</li>";
echo "<li>Server resources exhausted</li>";
echo "</ul>";
?>
