<?php

namespace Celysium\Permission\Controllers;

use Celysium\Base\Controller\Controller;
use Celysium\Permission\Traits\Permissions;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * @throws Exception
     */
    public function roles(Request $request, int|string $user_id)
    {
        $model = config('permission.user.model');
        /** @var Permissions $user */
        $user = $model->query()->findOrFail($user_id);

        if (!$this->isPermissions($model)) {
            throw new Exception('model user dont use trait Permissions');
        }

        $request->validate([
            'roles'   => ['required', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        $user->attachRolesById($request->get('roles'));
    }

    /**
     * @throws Exception
     */
    public function permissions(Request $request, int|string $user_id)
    {
        $model = config('permission.user.model');
        /** @var Permissions $user */
        $user = $model->query()->findOrFail($user_id);

        if (!$this->isPermissions($model)) {
            throw new Exception('model user dont use trait Permissions');
        }

        $request->validate([
            'permissions'           => ['required', 'array'],
            'permissions.*'         => ['integer', 'exists:permissions,id'],
            'permissions.*.is_able' => ['required', 'boolean'],
        ]);

        $user->attachPermissionsById($request->get('permissions'));
    }

    /**
     * @param string $model
     * @return bool
     */
    protected function isPermissions(string $model): bool
    {
        return in_array(Permissions::class, class_uses_recursive($model));
    }
}