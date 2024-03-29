<?php

namespace Celysium\Permission\Controllers;

use Celysium\Base\Controller\Controller;
use Celysium\Permission\Traits\Permissions;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @param Model $user
     * @param callable|null $authorize
     * @return Model
     * @throws Exception
     */
    public function syncRolesById(Request $request, Model $user, callable $authorize = null): Model
    {
        if ($authorize) {
            $authorize();
        }

        if (!$this->isPermissions($user)) {
            throw new Exception('model user dont use trait Permissions');
        }

        $this->validate($request, [
            'roles'   => ['required', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        /** @var Permissions $user */
        $user->roles()->sync($request->get('roles'));

        return $user;
    }

    /**
     * @param Request $request
     * @param Model $user
     * @param callable|null $authorize
     * @return Model
     * @throws Exception
     */
    public function syncPermissionsById(Request $request, Model $user, callable $authorize = null): Model
    {
        if ($authorize) {
            $authorize();
        }

        if (!$this->isPermissions($user::class)) {
            throw new Exception('model user dont use trait Permissions');
        }

        $this->validate($request, [
            'permissions'           => ['required', 'array'],
            'permissions.*.id'      => ['integer', 'exists:permissions,id'],
            'permissions.*.is_able' => ['required', 'boolean'],
        ]);

        $permissions = [];
        foreach ($request->get('permissions') as $permission) {
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