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
            $key = str_replace('{user}', $user->id, config("permission.cache.key_role_user"));
            Cache::forget($key);
        });
    }

    /**
     * @param string $name
     * @return array
     */
    public function allowPermissions(string $name): array
    {
        $role = RoleModel::with('permissions')->where('name', $name)->first();
        return $role?->permissions->pluck('namespaces', 'name')->toArray();
    }

    /**
     * @param string $name
     * @param bool $refresh
     * @return array
     */
    public static function cachePermissions(string $name, bool $refresh = false): array
    {
        $key = str_replace('{role}', $name, config("permission.cache.key_role_permission"));
        if ($refresh) {
            Cache::forget($key);
        }
        return Cache::store(config('permission.cache.driver'))
            ->rememberForever($key, fn() => $this->allowPermissions($name));
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
