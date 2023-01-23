<?php

namespace Celysium\ACL\Models;

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

        Cache::store(config('acl.cache.driver'))
            ->put($this->name, $permissions);
    }

    public function refreshCacheOnDelete(): void
    {
        Cache::store(config('acl.cache.driver'))
            ->forget($this->name);
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
        if($throw) {
            return [];
        }

        $notExists = array_diff($names, $items->pluck('name')->toArray());

        throw new ModelNotFoundException('Not found roles name ' . implode(', ', $notExists));
    }
}
