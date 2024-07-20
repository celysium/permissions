<?php

namespace Celysium\Permission\Traits;

use Celysium\Permission\Models\Permission;
use Celysium\Permission\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * @property integer $id
 * @property Collection $roles
 * @property Collection $permissions
 */
trait Permissions
{
    /**
     * Get roles of user
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        /** @var Model $this */
        return $this->belongsToMany(
            Role::class,
            'role_user',
            config('permission.user.foreign_key'),
            'role_id'
        );
    }

    /**
     * Assign roles to user
     * @param array $names
     * @return array
     */
    public function attachRoles(array $names): array
    {
        return $this->attachRolesById(Role::getIds($names));
    }

    /**
     * Assign roles to user
     * @param array $ids
     * @return array
     */
    public function attachRolesById(array $ids): array
    {
        $this->roles()->syncWithoutDetaching($ids);

        return $this->cacheRoles(true);
    }

    /**
     * Detach roles user
     * @param array $names
     * @return array
     */
    public function detachRoles(array $names): array
    {
        return $this->detachRolesById(Role::getIds($names));
    }

    /**
     * Detach roles user
     * @param array $ids
     * @return array
     */
    public function detachRolesById(array $ids): array
    {
        $this->roles()->detach($ids);

        return $this->cacheRoles(true);
    }

    /**
     * Get list roles user
     * @return array
     */
    public function allowsRoles(): array
    {
        return $this->roles()->get(['name'])->pluck('name')->toArray();
    }

    /**
     * Cache roles user
     * @param bool $refresh
     * @return array
     */
    public function cacheRoles(bool $refresh = false): array
    {
        $key = str_replace('{user}', $this->id, config("permission.cache.key_role_user"));
        if ($refresh) {
            Cache::forget($key);
        }
        return Cache::store(config('permission.cache.driver'))
            ->remember($key, config('permission.cache.lifetime'), fn() => $this->allowsRoles());
    }

    /**
     * Check access role user
     * @param ...$names
     * @return bool
     */
    public function hasRoles(...$names): bool
    {
        return (bool)count(array_intersect($names, $this->cacheRoles()));
    }

    /**
     * Get permissions user
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        /** @var Model $this */
        return $this->belongsToMany(
            Permission::class,
            'permission_user',
            config('permission.user.foreign_key'),
            'permission_id'
        )
            ->withPivot(['can']);
    }

    /**
     * Get list permissions of user
     * @return array
     */
    public function allowsCachePermissions(): array
    {
        $roles = $this->cacheRoles();
        $permissions = [];
        foreach ($roles as $role) {
            $permissions = array_merge($permissions, Role::cachePermissions($role));
        }

        $customs = $this->cachePermissions();
        foreach ($customs['allows'] as $name => $namespaces) {
            $permissions[$name] = $namespaces;
        }
        foreach ($customs['denies'] as $name) {
            unset($permissions[$name]);
        }
        return $permissions;
    }

    /**
     * @param bool $refresh
     * @return array
     */
    protected function cachePermissions(bool $refresh = false): array
    {
        $key = str_replace('{user}', $this->id, config("permission.cache.key_permission_user"));
        if ($refresh) {
            Cache::forget($key);
        }
        return Cache::store(config('permission.cache.driver'))
            ->remember($key, config('permission.cache.lifetime'), fn() => $this->allowsPermissions());
    }

    /**
     * Get list permissions of user
     * @return array
     */
    public function allowsPermissions(): array
    {
        $denies = $this->permissions()
            ->where('can', false)
            ->get(['name'])
            ->pluck('name')
            ->toArray();

        $allows = $this->permissions()
            ->where('can', true)
            ->get(['namespaces', 'name'])
            ->pluck('namespaces', 'name')
            ->toArray();

        return compact('denies', 'allows');
    }

    /**
     * Assign permissions user
     * @param array $names
     * @return void
     */
    public function attachPermissions(array $names): void
    {
        $this->attachPermissionsById(Role::getIds($names));
    }

    /**
     * Assign permissions user
     * @param array $ids
     * @return void
     */
    public function attachPermissionsById(array $ids): void
    {
        $this->permissions()->syncWithoutDetaching($ids);

        $this->cachePermissions(true);
    }

    /**
     * Detach permissions  user
     * @param array $names
     * @return void
     */
    public function detachPermissions(array $names): void
    {
        $this->detachPermissionsById(Permission::getIds($names));
    }

    /**
     * Detach permissions user
     * @param array $ids
     * @return void
     */
    public function detachPermissionsById(array $ids): void
    {
        $this->permissions()->detach($ids);

        $this->cachePermissions(true);
    }

    /**
     * Check access permissions for user
     *
     * @param ...$names
     * @return bool
     */
    public function hasPermissions(...$names): bool
    {
        return (bool)count(array_intersect($names, array_keys($this->allowsCachePermissions())));
    }
}
