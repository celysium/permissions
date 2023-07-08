<?php

namespace Celysium\Permission\Repositories\Role;

use Celysium\Base\Repository\BaseRepositoryInterface;
use Celysium\Permission\Models\Role;

interface RoleRepositoryInterface extends BaseRepositoryInterface
{
    public function syncPermissions(Role $role, array $parameters): Role;
}