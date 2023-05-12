<?php

namespace Celysium\Permission\Repositories\Permission;

use Celysium\Base\Repository\BaseRepository;
use Celysium\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Builder;

class PermissionRepository extends BaseRepository implements PermissionRepositoryInterface
{
    public function __construct(Permission $permission)
    {
        parent::__construct($permission);
    }

    public function conditions(Builder $query): array
    {
        return [
            'name'  => fn($value) => $query->where('name', 'like', "%$value%"),
            'title' => fn($value) => $query->where('title', 'like', "%$value%"),
        ];
    }
}