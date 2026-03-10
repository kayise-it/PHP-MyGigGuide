<?php
// Clear OPcache for web process
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully at " . date('Y-m-d H:i:s') . "\n";
} else {
    echo "OPcache not available\n";
}

// Test write to debug log
file_put_contents('/var/www/mygigguide/.cursor/debug.log', "Web PHP test write at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
echo "Test log written\n";


