<?php

namespace Celysium\ACL\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;


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

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'permission_roles',
            'role_id',
            'permission_id'
        );
    }

    public function users()
    {
        return $this->belongsToMany(
            config('acl.models.user'),
            config('acl.database.role_users.foreign_key'),
            'permission_id',
            config('acl.database.permission_users.user_foreign_key')
        );
    }
}
