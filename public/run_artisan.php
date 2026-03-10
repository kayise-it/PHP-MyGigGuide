<?php

// TEMPORARY deployment helper. Delete after running.
ini_set('display_errors', 1);
error_reporting(E_ALL);

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';
$app = require_once $root.'/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;

$kernel = $app->make(Kernel::class);

function run($cmd)
{
    echo "<pre>Running: artisan $cmd\n";
    try {
        $code = Artisan::call($cmd);
        echo Artisan::output();
        echo "Exit: $code\n";
    } catch (Throwable $e) {
        echo 'ERROR: '.$e->getMessage()."\n";
        echo nl2br($e->getTraceAsString());
    }
    echo '</pre><hr>';
}

// Ensure storage permissions
@chmod($root.'/storage', 0755);
@chmod($root.'/bootstrap/cache', 0755);

if (empty(env('APP_KEY'))) {
    run('key:generate');
}
run('config:clear');
run('cache:clear');
run('route:clear');
run('view:clear');
run('migrate --force');
run('config:cache');
run('route:cache');
run('view:cache');

echo '<strong>Done. Remove public/run_artisan.php now.</strong>';
