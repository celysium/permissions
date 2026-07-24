<?php

namespace Celysium\Permission\Traits;

use Celysium\Permission\Models\Role as RoleModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;

/**
 * @property-read array $permissions_name
 */
trait Role
{
    /**
     * @return array
     */
    public function getPermissionsNameAttribute(): array
    {
        return static::cachePermissionsName($this->name);
    }

    /**
     * @param bool $refresh
     * @return array
     */
    public function getPermissionsName(bool $refresh = false): array
    {
        return static::cachePermissionsName($this->name, $refresh);
    }

    /**
     * @param string $name
     * @param bool $refresh
     * @return array
     */
    public static function cachePermissionsName(string $name, bool $refresh = false): array
    {
        $key = str_replace('{role}', $name, config("permission.cache.key_role_permissions"));
        if ($refresh) {
            Cache::store(config('permission.cache.driver'))->forget($key);
        }
        return Cache::store(config('permission.cache.driver'))
            ->remember($key, config('permission.cache.lifetime'), fn() => static::permissionsName($name));
    }

    /**
     * @param string $name
     * @return array
     */
    public static function permissionsName(string $name): array
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
