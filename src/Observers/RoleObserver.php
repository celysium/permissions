<?php

namespace Celysium\Permission\Observers;

use Celysium\Permission\Models\Role;

class RoleObserver
{
    public function created(Role $role): void
    {
        Role::cachePermissions($role->name, true);
    }

    public function updated(Role $role): void
    {
        $role->resetCacheUsers();
        Role::cachePermissions($role->name, true);
    }

    public function deleted(Role $role): void
    {
        $role->resetCacheUsers();
        Role::cachePermissions($role->name, true);
    }
}
