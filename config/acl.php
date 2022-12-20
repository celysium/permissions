<?php

return [
    'cache_driver' => env('ACL_CACHE_DRIVER', 'database'),
    'cache_time' => env('ACL_CACHE_TIME', 60),

    'user' => [
        'model' => \App\Models\User::class,
        'table' => 'users',
        'foreign_key' => 'users',
    ]

];
