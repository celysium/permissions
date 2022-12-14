<?php

namespace App\acl\acl\src;

use App\acl\acl\src\Observers\PermissionObserver;
use Celysium\ACL\Models\Permission;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class AclEventServiceProvider extends EventServiceProvider
{
    protected $observers = [
        Permission::class => [PermissionObserver::class]
    ];
}
