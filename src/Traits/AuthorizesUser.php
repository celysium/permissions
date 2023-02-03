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

    public function assignPermissions(array $ids): array
    {
        return $this->permissions()->sync($ids);
    }

    public function hasRoles(array ...$names): bool
    {
        return $this->roles()->whereIn('name', $names)->exists();
    }

    public function hasRolesCache(array $roles): bool
    {
        $userRoles = Cache::store(config('acl.cache.driver'))
            ->remember(
                "acl.role.$this->id",
                config('acl.cache.lifetime'),
                fn() => $this->roles()->pluck('name')->toArray()
            );

        return (bool)count(array_intersect($roles, $userRoles));
    }

    public function hasPermissions(array ...$names): bool
    {
        return $this->permissions()->whereIn('name', $names)->exists();
    }

    public function hasPermissionsCache(array $permissions): bool
    {
        if (config('acl.shop_mode') === 'enterprise') {
            //
        }

        $userPermissions = Cache::store(config('acl.cache.driver'))
            ->remember(
                "acl.permission.$this->id",
                config('acl.cache.lifetime'),
                fn () => $this->rolePermissions()
            );

        return (bool)count(array_intersect($permissions, $userPermissions));
    }
}
