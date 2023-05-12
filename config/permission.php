<?php

return [
    'cache' => [
        'driver'         => env('PERMISSION_CACHE_DRIVER', env('CACHE_DRIVER')),
        'lifetime'       => env('PERMISSION_CACHE_LIFETIME', 60),
        'key_permission' => "permission.{user_id}",
        'key_role'       => "role.{user_id}",
    ],

    'user' => [
        'model'       => \App\Models\User::class,
        'table'       => 'users',
        'foreign_key' => 'user_id',
        'relation_id' => 'id',
        'type'        => 'unsignedBigInteger'
    ]
];
