<?php

namespace Celysium\ACL\Observers;

use Celysium\ACL\Models\Permission;

class PermissionObserver
{
    public function created(Permission $permission): void
    {
        $permission->refreshCache();
    }

    public function updated(Permission $permission): void
    {
        $permission->refreshCache();
    }

    public function deleted(Permission $permission): void
    {
        $permission->refreshCacheOnDelete();
    }
}
