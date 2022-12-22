<?php

namespace Celysium\ACL\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

trait AuthorizesUser
{
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

    public function hasRolesCache($roles): bool
    {
        $userRoles = Cache::store(config('acl.cache.driver'))
            ->remember(
                "acl.role.$this->id",
                config('acl.cache.life_time'),
                fn() => $this->roles()->pluck('name')->toArray()
            );

        return (bool)count(array_intersect($roles, $userRoles));
    }

    public function hasPermissions(array ...$names): bool
    {
        return $this->permissions()->whereIn('name', $names)->exists();
    }

    public function hasPermissionsCache($permissions): bool
    {
        $userPermissions = Cache::store(config('acl.cache.driver'))
            ->remember(
                "acl.permission.$this->id",
                config('acl.cache.life_time'),
                fn() => $this->permissions()->pluck('name')->toArray()
            );

        return (bool)count(array_intersect($permissions, $userPermissions));
    }
}
