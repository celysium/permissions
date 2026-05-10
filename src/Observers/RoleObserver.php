<?php

namespace Celysium\Permission\Observers;

use Celysium\Permission\Models\Role;

class RoleObserver
{
    public function created(Role $role): void
    {
        $role->getPermissions(true);
    }

    public function updated(Role $role): void
    {
        $role->resetCacheUsers();
        $role->getPermissions(true);
    }

    public function deleted(Role $role): void
    {
        $role->resetCacheUsers();
        $role->getPermissions(true);
    }
}
