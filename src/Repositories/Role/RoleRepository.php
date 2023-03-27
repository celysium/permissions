<?php

namespace Celysium\Permission\Repositories\Role;

use Celysium\Base\Repository\BaseRepository;
use Celysium\Permission\Models\Role;

class RoleRepository extends BaseRepository
{
    public function __construct(Role $role)
    {
        parent::__construct($role);
    }

    public function rules(): array
    {
        return [
            'name'  => fn($query, $value) => $query->where('name', 'like', "%$value%"),
            'title' => fn($query, $value) => $query->where('title', 'like', "%$value%"),
        ];
    }
}