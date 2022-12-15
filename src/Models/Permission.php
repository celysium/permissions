<?php

namespace Celysium\ACL\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * @property integer $id
 * @property string $name
 * @property string $title
 * @property Collection $roles
 * @property Collection $users
 */
class Permission extends Model
{
    protected $fillable = ['name', 'title'];

    public $timestamps = false;

    public function getTable()
    {
        return config('acl.models.permission');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'permission_roles',
            'permission_id',
            'role_id'
        );
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            config('acl.user.model'),
            'permission_users',
            'permission_id',
            config('acl.user.foreign_key')
        );
    }

    public function refreshCache(): void
    {
        foreach ($this->roles as $role) {
            Cache::store(config('acl.cache.storage'))
                ->put($role->name, $this->name);
        }
    }

    public function refreshCacheOnDelete(): void
    {
        /** @var Role $role */
        foreach ($this->roles as $role) {
            $rolePermissions = $role->permissions()
                ->pluck('name')
                ->toArray();

            Cache::store(config('acl.cache.storage'))
                ->put($role->name, $rolePermissions);
        }
    }
}
