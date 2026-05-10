<?php

return [
    'cache' => [
        'driver'               => env('PERMISSION_CACHE_DRIVER', env('CACHE_DRIVER')),
        'lifetime'             => env('PERMISSION_CACHE_LIFETIME', 60),
        'key_role_permissions' => "role_{role}_permissions",
        'key_user_roles'       => "user_{user}_roles",
        'key_user_permissions' => "user_{user}_permissions",
    ],
    'model'  => [
        'name'        => 'user',
        'class'       => \App\Models\User::class,
        'table'       => 'users',
        'foreign_key' => 'user_id',
        'relation_id' => 'id',
        'type'        => 'unsignedBigInteger'
    ],
    'permissions' => [

    ]
];
