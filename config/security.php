<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Signed Preview URL TTL
    |--------------------------------------------------------------------------
    |
    | Number of minutes that temporary signed preview URLs remain valid.
    |
    */
    'preview_url_ttl_minutes' => (int) env('PREVIEW_URL_TTL_MINUTES', 15),
];

