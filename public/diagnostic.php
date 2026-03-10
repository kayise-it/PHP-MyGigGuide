<?php
echo "<h1>My Gig Guide Diagnostic</h1>";
echo "<h2>Server Information</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Current Time: " . date('Y-m-d H:i:s') . "<br>";

echo "<h2>File System Check</h2>";
$required_files = [
    '../vendor/autoload.php',
    '../bootstrap/app.php',
    '../routes/web.php',
    '../app/Http/Controllers/HomeController.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file missing<br>";
    }
}

echo "<h2>Laravel Application Test</h2>";
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
    } else {
        echo "❌ Composer autoloader not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Error loading Laravel: " . $e->getMessage() . "<br>";
}

echo "<h2>Database Test</h2>";
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=mygigguide', 'root', '');
    echo "✅ Database connection successful<br>";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

echo "<h2>Environment Check</h2>";
if (file_exists('../.env')) {
    echo "✅ .env file exists<br>";
} else {
    echo "❌ .env file missing - this is likely the cause of the 503 error<br>";
}

echo "<h2>Permissions Check</h2>";
$writable_dirs = ['../storage', '../bootstrap/cache'];
foreach ($writable_dirs as $dir) {
    if (is_writable($dir)) {
        echo "✅ $dir is writable<br>";
    } else {
        echo "❌ $dir is not writable<br>";
    }
}
?>
