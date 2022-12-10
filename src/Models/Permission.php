<?php

namespace Celysium\ACL\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'title'];

    public $timestamps = false;

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
}
