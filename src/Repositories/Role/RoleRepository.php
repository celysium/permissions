<?php

namespace Celysium\Permission\Repositories\Role;

use Celysium\Helper\Repository\BaseRepository;
use Celysium\Permission\Models\Role;
use Illuminate\Database\Eloquent\Builder;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    protected static string $entity = Role::class;


    public function conditions(Builder $query): array
    {
        return [
            'name' => 'like',
        ];
    }
}