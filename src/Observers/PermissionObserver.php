<?php

namespace Celysium\Permission\Observers;

use Celysium\Permission\Models\Permission;

class PermissionObserver
{
    public function created(Permission $permission): void
    {
        $permission->resetCacheUsers();
        $permission->resetCacheRoles();
    }

    public function updated(Permission $permission): void
    {
        $permission->resetCacheUsers();
        $permission->resetCacheRoles();
    }

    public function deleted(Permission $permission): void
    {
        $permission->resetCacheUsers();
        $permission->resetCacheRoles();
    }
}
