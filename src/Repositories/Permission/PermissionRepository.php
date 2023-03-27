<?php

namespace Celysium\Permission\Repositories\Permission;

use Celysium\Base\Repository\BaseRepository;
use Celysium\Permission\Models\Permission;

class PermissionRepository extends BaseRepository
{
    public function __construct(Permission $permission)
    {
        parent::__construct($permission);
    }

    public function rules(): array
    {
        return [
            'name'  => fn($query, $value) => $query->where('name', 'like', "%$value%"),
            'title' => fn($query, $value) => $query->where('title', 'like', "%$value%"),
        ];
    }
}