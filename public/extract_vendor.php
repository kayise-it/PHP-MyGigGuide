<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$archive = __DIR__.'/../vendor.tar.gz';
$target = __DIR__.'/../vendor';

if (! file_exists($archive)) {
    exit('Archive not found');
}

// Create target dir
if (! is_dir($target)) {
    mkdir($target, 0755, true);
}

try {
    $phar = new PharData($archive);
    $phar->decompress(); // creates vendor.tar
    $tarPath = __DIR__.'/../vendor.tar';
    $tar = new PharData($tarPath);
    $tar->extractTo($target, null, true);
    echo 'OK';
} catch (Throwable $e) {
    echo 'ERR: '.$e->getMessage();
}
