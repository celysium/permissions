<?php

namespace Celysium\ACL\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property string $name
 * @property string $title
 */
class Permission extends Model
{
    protected $fillable = ['name', 'title'];

    public $timestamps = false;

    public function getTable()
    {
        return config('acl.models.permission');
    }

    public function roles()
    {
        return $this->belongsToMany(
            config('acl.models.role'),
            config('acl.database.permission_roles.table_name'),
            config('acl.database.permission.foreign_key'),
            config('acl.database.role.foreign_key')
        );
    }

    public function scopeName($query, $name)
    {
        return $query->where('name', $name);
    }

    public function users()
    {
        return $this->belongsToMany(
            config('acl.models.user'),
            config('acl.database.role_users.foreign_key'),
            config('acl.database.role.foreign_key'),
            config('acl.database.permission_users.user_foreign_key')
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
