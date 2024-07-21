<?php

namespace Celysium\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
    use \Celysium\Permission\Traits\Role;

    protected $fillable = ['name'];

    public $timestamps = false;

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'permission_role',
            'role_id',
            'permission_id'
        );
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.user.model'),
            'role_user',
            'role_id',
            config('permission.user.foreign_key')
        );
    }
}
