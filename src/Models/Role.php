<?php

namespace Celysium\ACL\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property string $name
 * @property string $title
 */
class Role extends Model
{
    protected $fillable = ['name', 'title'];
    public $timestamps = false;

    public function getTable()
    {
        return config('acl.models.role');
    }

    public function permissions()
    {
        return $this->belongsToMany(
            config('acl.models.permission'),
            config('acl.database.permission_roles.table_name'),
            config('acl.database.role.foreign_key'),
            config('acl.database.permission.foreign_key')
        );
    }

    public function users()
    {
        return $this->belongsToMany(
            config('acl.models.user'),
            config('acl.database.role_users.foreign_key'),
            config('acl.database.permission.foreign_key'),
            config('acl.database.permission_users.user_foreign_key')
        );
    }

    public function refreshCache(): void
    {
        $permissions = $this->permissions()
            ->pluck('name')
            ->toArray();

        Cache::store(config('acl.cache.storage'))
            ->put($this->name, $permissions);
    }

    public function refreshCacheOnDelete(): void
    {
        Cache::store(config('acl.cache.storage'))
            ->forget($this->name);
    }
}
