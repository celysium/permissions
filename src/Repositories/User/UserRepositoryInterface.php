<?php

namespace Celysium\Permission\Repositories\User;

use Illuminate\Database\Eloquent\Model;

interface UserRepositoryInterface
{
    public function syncRoles(array $parameters, Model $user): Model;

    public function syncPermissions(array $parameters, Model $user): Model;
}