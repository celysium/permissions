<?php

namespace Celysium\ACL;

use Celysium\ACL\Models\Role;
use Celysium\ACL\Observers\PermissionObserver;
use Celysium\ACL\Models\Permission;
use Celysium\ACL\Observers\RoleObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class AclEventServiceProvider extends EventServiceProvider
{
    protected $observers = [
        Permission::class => [PermissionObserver::class],
        Role::class => [RoleObserver::class]
    ];
}
