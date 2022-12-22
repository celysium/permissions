<?php

return [
    'shop_mode' => env('SHOP_MODE', 'light'),

    'cache' => [
        'driver' => env('ACL_CACHE_DRIVER', 'database'),

        'life_time' => env('ACL_CACHE_TIME', 60),
    ],

    'user' => [
        'model' => \App\Models\User::class,
        'table' => 'users',
        'foreign_key' => 'users',
    ]

];
