<?php
/**
 * Complete 503 Fix Script
 * Access: https://mygigguide.co.za/fix_503_complete.php
 * 
 * This script will:
 * 1. Create/verify .env file exists
 * 2. Generate APP_KEY if missing
 * 3. Fix permissions
 * 4. Clear all caches
 * 5. Test database connection
 * 6. Verify Laravel can bootstrap
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>503 Fix - My Gig Guide</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;background:#f5f5f5;}";
echo "h1{color:#d32f2f;}h2{color:#1976d2;margin-top:30px;}";
echo ".success{color:green;font-weight:bold;}.error{color:red;font-weight:bold;}";
echo ".info{background:#e3f2fd;padding:10px;border-left:4px solid #2196f3;margin:10px 0;}";
echo "ul{line-height:1.8;}code{background:#f0f0f0;padding:2px 6px;border-radius:3px;}</style></head><body>";

echo "<h1>🔧 Complete 503 Error Fix</h1>";
echo "<p>Fixing all common causes of Service Unavailable errors...</p>";

$baseDir = dirname(__DIR__);
$errors = [];
$success = [];

// Step 1: Verify/Create .env file
echo "<h2>Step 1: Environment File (.env)</h2>";
$envPath = $baseDir . '/.env';

if (!file_exists($envPath)) {
    echo "<p class='error'>❌ .env file missing - creating it...</p>";
    
    $envContent = 'APP_NAME="My Gig Guide"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://mygigguide.co.za

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ecotribe_mygigguide
DB_USERNAME=ecotribe_08600
DB_PASSWORD=

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_FROM_ADDRESS="hello@mygigguide.co.za"
MAIL_FROM_NAME="${APP_NAME}"';

    if (file_put_contents($envPath, $envContent)) {
        echo "<p class='success'>✅ .env file created</p>";
        $success[] = ".env file created";
    } else {
        echo "<p class='error'>❌ Failed to create .env file</p>";
        $errors[] = "Could not create .env file";
    }
} else {
    echo "<p class='success'>✅ .env file exists</p>";
}

// Step 2: Generate APP_KEY if missing
echo "<h2>Step 2: Application Key (APP_KEY)</h2>";
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    
    if (preg_match('/^APP_KEY=$/m', $envContent) || !preg_match('/^APP_KEY=/m', $envContent)) {
        echo "<p>Generating new APP_KEY using Laravel artisan...</p>";
        
        // Try to use Laravel's artisan command to generate the key properly
        if (file_exists($baseDir . '/vendor/autoload.php')) {
            try {
                require_once $baseDir . '/vendor/autoload.php';
                $app = require_once $baseDir . '/bootstrap/app.php';
                
                // Use Laravel's key generation
                $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
                $kernel->call('key:generate', ['--force' => true]);
                
                echo "<p class='success'>✅ APP_KEY generated using artisan</p>";
                $success[] = "APP_KEY generated via artisan";
            } catch (Exception $e) {
                echo "<p>⚠️ Could not use artisan, generating manually...</p>";
                
                // Fallback: Generate manually
                $key = 'base64:' . base64_encode(random_bytes(32));
                
                if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
                    $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $envContent);
                } else {
                    $envContent = preg_replace('/^(APP_ENV=.*)$/m', '$1' . "\n" . 'APP_KEY=' . $key, $envContent);
                }
                
                if (file_put_contents($envPath, $envContent)) {
                    echo "<p class='success'>✅ APP_KEY generated manually</p>";
                    $success[] = "APP_KEY generated manually";
                } else {
                    echo "<p class='error'>❌ Failed to save APP_KEY</p>";
                    $errors[] = "Could not save APP_KEY";
                }
            }
        } else {
            // Generate manually if Laravel not available
            $key = 'base64:' . base64_encode(random_bytes(32));
            
            if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
                $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $envContent);
            } else {
                $envContent = preg_replace('/^(APP_ENV=.*)$/m', '$1' . "\n" . 'APP_KEY=' . $key, $envContent);
            }
            
            if (file_put_contents($envPath, $envContent)) {
                echo "<p class='success'>✅ APP_KEY generated manually</p>";
                $success[] = "APP_KEY generated manually";
            } else {
                echo "<p class='error'>❌ Failed to save APP_KEY</p>";
                $errors[] = "Could not save APP_KEY";
            }
        }
    } else {
        echo "<p class='success'>✅ APP_KEY already set</p>";
    }
}

// Step 3: Create required directories
echo "<h2>Step 3: Required Directories</h2>";
$directories = [
    $baseDir . '/storage',
    $baseDir . '/storage/framework',
    $baseDir . '/storage/framework/sessions',
    $baseDir . '/storage/framework/views',
    $baseDir . '/storage/framework/cache',
    $baseDir . '/storage/logs',
    $baseDir . '/bootstrap/cache'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<p class='success'>✅ Created: " . basename($dir) . "</p>";
        } else {
            echo "<p class='error'>❌ Failed to create: " . basename($dir) . "</p>";
            $errors[] = "Could not create directory: $dir";
        }
    } else {
        echo "<p>✅ Exists: " . basename($dir) . "</p>";
    }
}

// Step 4: Fix permissions
echo "<h2>Step 4: Setting Permissions</h2>";
$permissionDirs = [
    $baseDir . '/storage' => 0755,
    $baseDir . '/bootstrap/cache' => 0755,
    $baseDir . '/storage/framework' => 0755,
    $baseDir . '/storage/framework/sessions' => 0755,
    $baseDir . '/storage/framework/views' => 0755,
    $baseDir . '/storage/framework/cache' => 0755,
    $baseDir . '/storage/logs' => 0755,
];

foreach ($permissionDirs as $dir => $perm) {
    if (is_dir($dir)) {
        if (chmod($dir, $perm)) {
            echo "<p>✅ Permissions set for: " . basename($dir) . "</p>";
        } else {
            echo "<p class='error'>❌ Failed to set permissions for: " . basename($dir) . "</p>";
        }
    }
}

// Step 5: Clear cache files
echo "<h2>Step 5: Clearing Cache</h2>";
$cacheFiles = [
    $baseDir . '/bootstrap/cache/config.php',
    $baseDir . '/bootstrap/cache/routes.php',
    $baseDir . '/bootstrap/cache/services.php',
    $baseDir . '/bootstrap/cache/packages.php',
];

foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "<p class='success'>✅ Cleared: " . basename($file) . "</p>";
        } else {
            echo "<p class='error'>❌ Failed to clear: " . basename($file) . "</p>";
        }
    }
}

// Step 6: Test Laravel bootstrap
echo "<h2>Step 6: Testing Laravel Bootstrap</h2>";
if (file_exists($baseDir . '/vendor/autoload.php')) {
    echo "<p class='success'>✅ Composer autoloader found</p>";
    
    try {
        require_once $baseDir . '/vendor/autoload.php';
        echo "<p class='success'>✅ Autoloader loaded</p>";
        
        if (file_exists($baseDir . '/bootstrap/app.php')) {
            echo "<p class='success'>✅ Bootstrap file exists</p>";
        } else {
            echo "<p class='error'>❌ Bootstrap file missing</p>";
            $errors[] = "Bootstrap file missing";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error loading Laravel: " . htmlspecialchars($e->getMessage()) . "</p>";
        $errors[] = "Laravel bootstrap error: " . $e->getMessage();
    }
} else {
    echo "<p class='error'>❌ Composer autoloader not found - run 'composer install'</p>";
    $errors[] = "Composer dependencies not installed";
}

// Step 7: Clear Laravel caches using artisan
echo "<h2>Step 7: Clearing Laravel Caches</h2>";
if (file_exists($baseDir . '/vendor/autoload.php')) {
    try {
        require_once $baseDir . '/vendor/autoload.php';
        $app = require_once $baseDir . '/bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        
        $cacheCommands = ['config:clear', 'cache:clear', 'route:clear', 'view:clear'];
        foreach ($cacheCommands as $cmd) {
            try {
                $kernel->call($cmd);
                echo "<p class='success'>✅ Cleared: $cmd</p>";
            } catch (Exception $e) {
                echo "<p>⚠️ Could not run $cmd: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
        $success[] = "Laravel caches cleared";
    } catch (Exception $e) {
        echo "<p class='info'>⚠️ Could not clear Laravel caches: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p class='info'>💡 You may need to run these manually: <code>php artisan config:clear && php artisan cache:clear</code></p>";
    }
} else {
    echo "<p class='info'>⚠️ Laravel not available for cache clearing</p>";
}

// Step 8: Test database connection (if credentials are available)
echo "<h2>Step 8: Database Connection Test</h2>";
if (file_exists($envPath)) {
    $envVars = parse_ini_file($envPath);
    $dbHost = $envVars['DB_HOST'] ?? 'localhost';
    $dbName = $envVars['DB_DATABASE'] ?? '';
    $dbUser = $envVars['DB_USERNAME'] ?? '';
    $dbPass = $envVars['DB_PASSWORD'] ?? '';
    
    if (!empty($dbName) && !empty($dbUser)) {
        try {
            $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<p class='success'>✅ Database connection successful</p>";
        } catch (PDOException $e) {
            echo "<p class='error'>❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p class='info'>💡 This may cause 503 errors if Laravel tries to connect to the database on every request.</p>";
            $errors[] = "Database connection failed";
        }
    } else {
        echo "<p class='info'>⚠️ Database credentials not fully configured in .env</p>";
    }
}

// Final Summary
echo "<h2>📊 Summary</h2>";

if (empty($errors)) {
    echo "<div class='info'><h3>✅ All checks passed!</h3>";
    echo "<p>The application should now be working. Try accessing:</p>";
    echo "<ul>";
    echo "<li><a href='https://mygigguide.co.za/' target='_blank'>https://mygigguide.co.za/</a></li>";
    echo "<li><a href='https://mygigguide.co.za/events' target='_blank'>https://mygigguide.co.za/events</a></li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background:#ffebee;padding:15px;border-left:4px solid #f44336;'>";
    echo "<h3>⚠️ Issues Found:</h3><ul>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul></div>";
}

if (!empty($success)) {
    echo "<div style='background:#e8f5e9;padding:15px;border-left:4px solid #4caf50;margin-top:20px;'>";
    echo "<h3>✅ Fixed:</h3><ul>";
    foreach ($success as $item) {
        echo "<li>" . htmlspecialchars($item) . "</li>";
    }
    echo "</ul></div>";
}

echo "<h2>🔍 If Still Getting 503 Error</h2>";
echo "<p>The issue may be server-level. Check:</p>";
echo "<ul>";
echo "<li><strong>Web Server Status</strong> - Apache/Nginx should be running</li>";
echo "<li><strong>PHP-FPM Status</strong> - PHP-FPM service should be active</li>";
echo "<li><strong>Database Server</strong> - MySQL should be running</li>";
echo "<li><strong>Server Resources</strong> - Check memory/CPU usage (may be exhausted)</li>";
echo "<li><strong>Error Logs</strong> - Check <code>storage/logs/laravel.log</code> and web server error logs</li>";
echo "</ul>";

echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>If you have SSH access, run: <code>php artisan config:clear && php artisan cache:clear</code></li>";
echo "<li>Check web server error logs</li>";
echo "<li>Contact your hosting provider if the issue persists</li>";
echo "</ol>";

echo "</body></html>";
?>

