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
 * @property array $roles_name
 * @property array $permissions_name
 * @property-read  Collection<Role> $roles
 * @property-read  Collection<Permission> $permissions
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
            'role_' . config('permission.model.name'),
            config('permission.model.foreign_key'),
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

        return $this->cacheRolesName(true);
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

        return $this->cacheRolesName(true);
    }

    /**
     * Get list roles user
     * @return array
     */
    public function getRolesNameAttribute(): array
    {
        return $this->cacheRolesName();
    }

    /**
     * Cache roles user
     * @param bool $refresh
     * @return array
     */
    public function cacheRolesName(bool $refresh = false): array
    {
        $key = str_replace('{user}', $this->id, config("permission.cache.key_user_roles"));
        if ($refresh) {
            Cache::store(config('permission.cache.driver'))->forget($key);
        }
        return Cache::store(config('permission.cache.driver'))
            ->remember($key, config('permission.cache.lifetime'), fn() => $this->getRolesName());
    }

    /**
     * Get list roles user
     * @return array
     */
    public function getRolesName(): array
    {
        return $this->roles()->get(['name'])->pluck('name')->toArray();
    }

    /**
     * Check access role user
     * @param ...$names
     * @return bool
     */
    public function hasRoles(...$names): bool
    {
        return empty(array_diff($names, $this->cacheRolesName()));
    }

    /**
     * Get list permissions of user
     * @return array
     */
    public function getCachePermissions(): array
    {
        $permissions = [];
        foreach ($this->cacheRolesName() as $role) {
            $permissions = array_merge($permissions, Role::cachePermissionsName($role));
        }
        return $permissions;
    }

    /**
     * @return array
     */
    protected function getPermissionsNameAttribute(): array
    {
        return $this->getPermissionsName();
    }

    /**
     * @param bool $refresh
     * @return array
     */
    protected function getPermissionsName(bool $refresh = false): array
    {
        $key = str_replace('{user}', $this->id, config("permission.cache.key_user_permissions"));
        if ($refresh) {
            Cache::store(config('permission.cache.driver'))->forget($key);
        }
        return Cache::store(config('permission.cache.driver'))
            ->remember($key, config('permission.cache.lifetime'), fn() => $this->getCachePermissions());
    }

    /**
     * Check access permissions for user
     *
     * @param ...$names
     * @return bool
     */
    public function hasPermissions(...$names): bool
    {
        return empty(array_diff($names, $this->getCachePermissions()));
    }

    /**
     * @param string $name
     * @return array
     */
    public function getSubPermissions(string $name): array
    {
        $permissions = $this->getCachePermissions();

        $allows = [];
        foreach ($permissions as $permission) {
            if($permission == $name) {
                $allows[] = '*';
            }
            elseif (str_starts_with($permission, $name)) {
                $allows[] = str_replace($name . '.', '', $permission);
            }
        }
        return $allows;
    }
}
