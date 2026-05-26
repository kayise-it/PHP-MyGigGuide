<?php

return [
    /** Single-use app → web login links expire after this many seconds. */
    'ttl_seconds' => (int) env('APP_WEB_SESSION_TTL', 300),
];
