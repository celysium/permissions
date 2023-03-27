<?php

namespace Celysium\Permission\Models;

use Celysium\Permission\Traits\Permissions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
            config('permission.models.user'),
            'role_users',
            'permission_id',
            config('permission.user.foreign_key')
        );
    }

    public function refreshCache(): void
    {
        /** @var Permissions $user */
        foreach ($this->users as $user) {
            $key = str_replace('{user_id}', $user->id, config("permission.cache.key_role"));
            if(Cache::has($key)) {
                $user->cacheRole(true);
            }
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
            ->select(['id', 'name'])
            ->pluck('id', 'name')
            ->toArray();

        if(count($items) === count($names)) {
            return array_values($items);
        }

        if($throw) {
            $notExists = array_diff($names, array_keys($items));
            throw new ModelNotFoundException('Not found roles name ' . implode(', ', $notExists));
        }
        return array_values($items);
    }
}
