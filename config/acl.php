<?php

return [
    'shop_mode' => env('SHOP_MODE', 'lite'),

    'cache' => [
        'driver' => env('ACL_CACHE_DRIVER', 'database'),

        'lifetime' => env('ACL_CACHE_LIFETIME', 60),
    ],

    'user' => [
        'model' => \App\Models\User::class,
        'table' => 'users',
        'foreign_key' => 'users',
    ]

];
