<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Queubious Url
    |--------------------------------------------------------------------------
    |
    | This value is the url where queubious is hosted, users without a
    | queue token or a queue cookie will be redirected to this url.
    |
    */

    'url' => env('QUEUBIOUS_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Queubious Secret
    |--------------------------------------------------------------------------
    |
    | The queubious system will issue a secret for encrypting the
    | queue token which this middleware will parse
    | and production domains which access your API via a frontend SPA.
    |
    */

    'secret' => env('QUEUBIOUS_SECRET', null),

    /*
    |--------------------------------------------------------------------------
    | Cookie Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes until a cookie storing an
    | issued token will be considered expired. If this value is null,
    | cookies that contain queue tokens do not expire.
    |
    */

    'expiration' => env('QUEUBIOUS_COOKIE_TIMEOUT', 20),
];
