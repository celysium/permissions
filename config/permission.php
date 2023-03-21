<?php

return [
    'cache' => [
        'driver'   => env('Permission_CACHE_DRIVER', 'database'),
        'lifetime' => env('Permission_CACHE_LIFETIME', 60),
    ],

    'user' => [
        'model'       => \App\Models\User::class,
        'table'       => 'users',
        'foreign_key' => 'user_id',
        'relation_id' => 'id',
        'type'        => 'unsignedBigInteger'
    ]
];
