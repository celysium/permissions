<?php

namespace Celysium\ACL\Observers;

use Celysium\ACL\Models\Role;
use Illuminate\Support\Facades\Cache;

class RoleObserver
{
    public function created(Role $role): void
    {
        $this->refreshAclCache($role);
    }

    public function updated(Role $role): void
    {
        $this->refreshAclCache($role);
    }

    public function deleted(Role $role): void
    {
        Cache::store(env('ACL_CACHE_DRIVER', 'database'))
            ->forget($role->name);
    }

    protected function refreshAclCache(Role $role): void
    {
        $permissions = $role->permissions()
            ->pluck('name')
            ->toArray();

        Cache::store(env('ACL_CACHE_DRIVER', 'database'))
            ->put($role->name, $permissions);
    }
}
