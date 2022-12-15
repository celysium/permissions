<?php

return [
    'cache' => env('ACL_CACHE_DRIVER', 'database'),

    'user' => [
        'model' => \App\Models\User::class,
        'table' => 'users',
        'foreign_key' => 'users',
    ]

];
