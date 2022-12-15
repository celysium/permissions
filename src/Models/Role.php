<?php

namespace Celysium\ACL\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;


/**
 * @property integer $id
 * @property string $name
 * @property string $title
 * @property Collection $permissions
 * @property Collection $users
 */
class Role extends Model
{
    protected $fillable = ['name', 'title'];

    public $timestamps = false;

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'permission_roles',
            'role_id',
            'permission_id'
        );
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            config('acl.models.user'),
            'role_users',
            'permission_id',
            config('acl.user.foreign_key')
        );
    }

    public function refreshCache(): void
    {
        $permissions = $this->permissions()
            ->pluck('name')
            ->toArray();

        Cache::store(config('acl.cache_driver'))
            ->put($this->name, $permissions);
    }

    public function refreshCacheOnDelete(): void
    {
        Cache::store(config('acl.cache_driver'))
            ->forget($this->name);
    }
}
