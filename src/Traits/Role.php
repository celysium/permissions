<?php

namespace Celysium\Permission\Traits;

use Celysium\Permission\Models\Role as RoleModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;

trait Role
{
    public function resetCacheUsers(): void
    {
        $this->users()->get(['id'])->map(function ($user) {
            /** @var Permissions $user */
            $key = str_replace('{user}', $user->id, config("permission.cache.key_user_roles"));
            Cache::forget($key);
        });
    }

    /**
     * @param bool $refresh
     * @return array
     */
    public function getPermissions(bool $refresh = false): array
    {
        return static::cachePermissions($this->name, $refresh);
    }

    /**
     * @param string $name
     * @param bool $refresh
     * @return array
     */
    public static function cachePermissions(string $name, bool $refresh = false): array
    {
        $key = str_replace('{role}', $name, config("permission.cache.key_role_permissions"));
        if ($refresh) {
            Cache::forget($key);
        }
        return Cache::store(config('permission.cache.driver'))
            ->rememberForever($key, fn() => static::getPermissionsName($name));
    }

    /**
     * @param string $name
     * @return array
     */
    public static function getPermissionsName(string $name): array
    {
        /** @var RoleModel $role */
        $role = RoleModel::with('permissions')->where('name', $name)->first();
        if(empty($role)){
            return [];
        }
        return $role->permissions->pluck('name')->toArray();
    }

    /**
     * @param array $names
     * @param bool $silent
     * @return array
     */
    public static function getIds(array $names, bool $silent = true): array
    {
        $roles = static::query()
            ->whereIn('name', $names)
            ->select(['id', 'name'])
            ->pluck('id', 'name')
            ->toArray();

        if (count($roles) === count($names) || $silent) {
            return array_values($roles);
        }

        $notExists = array_diff($names, array_keys($roles));
        throw new ModelNotFoundException('Not found roles name ' . implode(', ', $notExists));
    }

}
