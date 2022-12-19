<?php

namespace Celysium\ACL\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function hasPermissions(array ...$names): bool
    {
        return $this->permissions()->whereIn('name', $names)->exists();
    }
}