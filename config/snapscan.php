<?php

return [

    /** Default tip chip amounts (ZAR) shown after a live song request. */
    'tip_amounts_zar' => array_map('intval', explode(',', env('SNAPSCAN_TIP_AMOUNTS_ZAR', '20,50,100,200'))),

    /** SnapScan payment base URL. */
    'payment_base_url' => 'https://pos.snapscan.io/qr',

];
