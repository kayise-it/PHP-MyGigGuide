<?php
// Clear OPcache for web process
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully at " . date('Y-m-d H:i:s') . "\n";
} else {
    echo "OPcache not available\n";
}

