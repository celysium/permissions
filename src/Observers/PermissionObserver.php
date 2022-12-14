<?php

namespace App\acl\acl\src\Observers;

use Celysium\ACL\Models\Permission;
use Illuminate\Support\Facades\Cache;

class PermissionObserver
{
        public function created(Permission $permission): void
        {
            $this->refreshAclCache($permission);
        }

        public function updated(Permission $permission): void
        {
            $this->refreshAclCache($permission);
        }

        public function deleted(Permission $permission): void
        {
            foreach ($permission->roles as $role) {
                $rolePermissions = $role->permissions()
                    ->pluck('name')
                    ->toArray();

                Cache::store(env('ACL_CACHE_DRIVER', 'database'))
                    ->put($rolePermissions);
            }
        }

        protected function refreshAclCache(Permission $permission): void
        {
            foreach ($permission->roles as $role) {
                Cache::store(env('ACL_CACHE_DRIVER', 'database'))
                    ->put($role->name, $permission->name);
            }
        }
}
