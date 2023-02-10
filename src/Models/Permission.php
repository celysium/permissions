<?php

namespace Celysium\ACL\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    /**
     * @param array $names
     * @param bool $throw
     * @return array
     */
    public static function getIds(array $names, bool $throw = false): array
    {
        $items = static::query()
            ->whereIn('name', $names)
            ->get(['id', 'name']);

        if($items->count() == count($names)) {
            return $items->pluck('id')->toArray();
        }

        $notExists = array_diff($names, $items->pluck('name')->toArray());
        if($throw) {
            return $notExists;
        }

        throw new ModelNotFoundException('Not found permission name ' . implode(', ', $notExists));
    }
}
