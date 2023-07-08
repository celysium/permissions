<?php

namespace Celysium\Permission\Repositories\User;


use Celysium\Permission\Traits\Permissions;
use Exception;
use Illuminate\Database\Eloquent\Model;

class UserRepository implements UserRepositoryInterface
{
    /**
     * @param array $parameters
     * @param Model $user
     * @return Model
     * @throws Exception
     */
    public function syncRoles(array $parameters, Model $user): Model
    {
        if (!$this->isPermissions($user)) {
            throw new Exception('model user dont use trait Permissions');
        }

        /** @var Permissions $user */
        $user->roles()->sync($parameters['roles']);

        return $user;
    }

    /**
     * @param array $parameters
     * @param Model $user
     * @return Model
     * @throws Exception
     */
    public function syncPermissions(array $parameters, Model $user): Model
    {
        if (!$this->isPermissions($user::class)) {
            throw new Exception('model user dont use trait Permissions');
        }

        $permissions = [];
        foreach ($parameters['permissions'] as $permission) {
            $permissions[$permission['id']] = $permission['is_able'];
        }

        /** @var Permissions $user */
        $user->permissions()->sync($permissions);

        return $user;
    }

    /**
     * @param $model
     * @return bool
     */
    protected function isPermissions($model): bool
    {
        return in_array(Permissions::class, class_uses_recursive($model));
    }
}