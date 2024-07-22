<?php

namespace Celysium\Permission\Models;

use Celysium\Permission\Traits\Permissions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * @property integer $id
 * @property string $name
 * @property array $namespaces
 * @property Collection $roles
 * @property Collection $users
 */
class Permission extends Model
{
    protected $fillable = [
        'id',
        'name',
    ];

    public $timestamps = false;

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'permission_role',
            'permission_id',
            'role_id'
        );
    }

    public function getNamespacesAttribute(): array
    {
        return explode('.', $this->name);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.user.model'),
            'permission_user',
            'permission_id',
            config('permission.user.foreign_key')
        );
    }

    public function resetCacheUsers(): void
    {
        /** @var Permissions $user */
        foreach ($this->users as $user) {
            $user->cachePermissions(true);
        }
    }

    public function resetCacheRoles(): void
    {
        /** @var Role $role */
        foreach ($this->roles as $role) {
            $role->cachePermissions(true);
        }
    }

    /**
     * @param array $names
     * @param bool $throw
     * @return array
     */
    public static function getIds(array $names, bool $throw = true): array
    {
        $items = static::query()
            ->whereIn('name', $names)
            ->select(['id', 'name'])
            ->pluck('id', 'name')
            ->toArray();

        if (count($items) === count($names) || !$throw) {
            return array_values($items);
        }

        $notExists = array_diff($names, array_keys($items));
        throw new ModelNotFoundException('Not found permission name ' . implode(', ', $notExists));
    }
}
