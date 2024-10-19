<?php

namespace Celysium\Permission\Repositories\Permission;

use Celysium\Helper\Repository\BaseRepository;
use Celysium\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Builder;

class PermissionRepository extends BaseRepository implements PermissionRepositoryInterface
{
    protected static string $entity = Permission::class;

    public function conditions(Builder $query): array
    {
        return [
            'name' => 'like',
        ];
    }
}