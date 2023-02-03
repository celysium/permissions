<?php

namespace Celysium\ACL\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * @property integer $id
 * @property Collection $roles
 * @property Collection $permissions
 */
trait AuthorizesUser
{
    /**
     * Get permissions of user
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        /** @var Model $this */
        return $this->belongsToMany(
            config('acl.user.model'),
            'permission_users',
            'user_id',
            'permission_id'
        )
            ->withPivot(['is_able']);
    }

    /**
     * Get roles of user
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        /** @var Model $this */
        return $this->belongsToMany(
            config('acl.user.model'),
            'role_users',
            'user_id',
            'role_id'
        );
    }


    /**
     * Assign roles to user
     *
     * @param array $ids
     * @return array
     */
    public function assignRole(array $ids): array
    {
        return $this->roles()->sync($ids);
    }

    /**
     * Assign permissions to user
     *
     * @param array $ids
     * @return array
     */
    public function assignPermissions(array $ids): array
    {
        return $this->permissions()->sync($ids);
    }

    /**
     * Get list roles of user
     *
     * @return array
     */
    public function allowsRoles(): array
    {
        return $this->roles()->pluck('name')->toArray();
    }

    /**
     * Cache roles of user
     *
     * @return array
     */
    public function cacheRole(): array
    {
        return Cache::store(config('acl.cache.driver'))
            ->remember(
                "acl.role.$this->id",
                config('acl.cache.lifetime'),
                fn () => $this->allowsRoles()
            );
    }

    /**
     * Check access role for user
     *
     * @param array ...$names
     * @return bool
     */
    public function hasRoles(array ...$names): bool
    {
        return (bool)count(array_intersect($names, $this->cacheRole()));
    }

    /**
     * Get list permissions of user
     *
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
                    array_filter(
                        $customPermissions,
                        fn($permission) => $permission)
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
     * Cache permissions of user
     *
     * @return array
     */
    public function cachePermissions(): array
    {
        return Cache::store(config('acl.cache.driver'))
            ->remember(
                "acl.permission.$this->id",
                config('acl.cache.lifetime'),
                fn () => $this->allowsPermissions()
            );
    }

    /**
     * Check access permissions for user
     *
     * @param array ...$names
     * @return bool
     */
    public function hasPermissions(array ...$names): bool
    {
        return (bool) count(array_intersect($names, $this->cachePermissions()));
    }
}