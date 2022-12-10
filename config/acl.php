<?php

return [

    'database' => [
        'role' => [
            'table_name' => 'roles',
            'foreign_key' => 'role_id',
        ],
        'permission' => [
            'table_name' => 'permissions',
            'foreign_key' => 'permission_id',
        ],
        'permission_roles' => [
            'table_name' => 'permission_roles',
        ],
        'role_users' => [
            'table_name' => 'role_users',
            'user_table_name' => 'users',
            'user_foreign_key' => 'user_id',
        ],
        'permission_users' => [
            'table_name' => 'permission_users',
            'user_table_name' => 'users',
            'user_foreign_key' => 'user_id',
        ],
    ]

];