<?php

$public = __DIR__.'/public/index.php';
if (file_exists($public)) {
    require $public;
    exit;
}
http_response_code(500);
echo 'Public index not found';
