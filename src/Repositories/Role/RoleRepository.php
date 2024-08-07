<?php

namespace Celysium\Permission\Repositories\Role;

use Celysium\Helper\Repository\BaseRepository;
use Celysium\Permission\Models\Role;
use Illuminate\Database\Eloquent\Builder;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    public function __construct(Role $role)
    {
        parent::__construct($role);
    }

    public function conditions(Builder $query): array
    {
        return [
            'name'   => fn($value) => $query->where('name', 'like', "%$value%"),
            'title'  => fn($value) => $query->where('title', 'like', "%$value%"),
            'status' => fn($value) => $query->where('status', $value),
        ];
    }
}