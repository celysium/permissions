<?php

namespace Celysium\ACL\Models;

use Illuminate\Database\Eloquent\Model;
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
        /** @var Role $role */
        foreach ($this->roles as $role) {
            $role->refreshCache();
        }
    }
}
