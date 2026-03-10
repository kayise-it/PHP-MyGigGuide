<?php

// TEMP: Run composer install from web. Remove after success.
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(900);
chdir(dirname(__DIR__));

function out($msg)
{
    echo '<pre>'.htmlspecialchars($msg)."</pre>\n";
    @ob_flush();
    @flush();
}

$php = PHP_BINARY ?: 'php';
$composer = file_exists('composer.phar') ? 'composer.phar' : (file_exists('composer') ? 'composer' : null);
if (! $composer) {
    out('composer not found');
    exit;
}

$cmd = "$php $composer install --no-dev --prefer-dist --optimize-autoloader 2>&1";
out('Running: '.$cmd);
$proc = popen($cmd, 'r');
if ($proc) {
    while (! feof($proc)) {
        out(fgets($proc));
    }
    $status = pclose($proc);
    out('Exit: '.$status);
} else {
    out('Failed to start composer process');
}
