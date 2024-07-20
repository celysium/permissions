<?php

return [
    'cache' => [
        'driver'              => env('PERMISSION_CACHE_DRIVER', env('CACHE_DRIVER')),
        'lifetime'            => env('PERMISSION_CACHE_LIFETIME', 60),
        'key_role_user'       => "role.permission.{user}",
        'key_role_permission' => "role.permission.{role}",
        'key_permission_user' => "role.permission.{user}",
    ],
    'user'  => [
        'model'       => \App\Models\User::class,
        'table'       => 'users',
        'foreign_key' => 'user_id',
        'relation_id' => 'id',
        'type'        => 'unsignedBigInteger'
    ]
];
