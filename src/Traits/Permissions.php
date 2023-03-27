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
            'role_users',
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

        return $this->cacheRole(true);
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

        return $this->cacheRole(true);
    }

    /**
     * Get list roles user
     * @return array
     */
    public function allowsRoles(): array
    {
        return $this->roles()->pluck('name')->toArray();
    }

    /**
     * Cache roles user
     * @param bool $force
     * @return array
     */
    public function cacheRole(bool $force = false): array
    {
        $key = str_replace('{user_id}', $this->id, config("permission.cache.key_role"));
        if ($force) {
            Cache::forget($key);
        }
        return Cache::store(config('permission.cache.driver'))
            ->remember(
                $key,
                config('permission.cache.lifetime'),
                fn () => $this->allowsRoles()
            );
    }

    /**
     * Check access role user
     * @param ...$names
     * @return bool
     */
    public function hasRoles(...$names): bool
    {
        return (bool)count(array_intersect($names, $this->cacheRole()));
    }

    /**
     * Check access just role user
     * @param string $name
     * @return bool
     */
    public function onlyRole(string $name): bool
    {
        $roles = $this->cacheRole();
        return count($roles) === 1 && current($roles) === $name;
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
            'permission_users',
            config('permission.user.foreign_key'),
            'permission_id'
        )
            ->withPivot(['is_able']);
    }

    /**
     * Get list permissions of user
     * @return array
     */
    public function allowsPermissions(): array
    {
        $permissions = [];
        /** @var Model $this */
        $withRoles = $this->load('roles.permissions');
        /** @var self $withRoles */
        foreach ($withRoles->roles as $role) {
            $permissions = array_merge($permissions, $role->permissions->pluck('name')->toArray());
        }

        $customPermissions = $this->permissions()
            ->select('permissions.name')
            ->pluck('permission_users.is_able', 'permissions.name')
            ->toArray();

        if(count($customPermissions)) {
            $permissions = array_merge(
                $permissions,
                array_keys(
                    array_filter($customPermissions)
                )
            );
            $permissions = array_diff(
                $permissions,
                array_keys(
                    array_filter(
                        $customPermissions,
                        fn($permission) => !$permission)
                )
            );
        }

        return $permissions;
    }

    /**
     * Cache permissions user
     * @param bool $force
     * @return array
     */
    public function cachePermissions(bool $force = false): array
    {
        $key = str_replace('{user_id}', $this->id, config("permission.cache.key_permission"));
        if ($force) {
            Cache::forget($key);
        }
        return Cache::store(config('permission.cache.driver'))
            ->remember(
                $key,
                config('permission.cache.lifetime'),
                fn () => $this->allowsPermissions()
            );
    }

    /**
     * Assign permissions user
     * @param array $names
     * @return array
     */
    public function attachPermissions(array $names): array
    {
        return $this->attachPermissionsById(Role::getIds($names));
    }

    /**
     * Assign permissions user
     * @param array $ids
     * @return array
     */
    public function attachPermissionsById(array $ids): array
    {
        $this->permissions()->syncWithoutDetaching($ids);

        return $this->cachePermissions(true);
    }

    /**
     * Detach permissions  user
     * @param array $names
     * @return array
     */
    public function detachPermissions(array $names): array
    {
        return $this->detachPermissionsById(Permission::getIds($names));
    }

    /**
     * Detach permissions user
     * @param array $ids
     * @return array
     */
    public function detachPermissionsById(array $ids): array
    {
        $this->permissions()->detach($ids);

        return $this->cachePermissions(true);
    }

    /**
     * Check access permissions for user
     *
     * @param ...$names
     * @return bool
     */
    public function hasPermissions(...$names): bool
    {
        return (bool) count(array_intersect($names, $this->cachePermissions()));
    }
}