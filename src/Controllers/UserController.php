<?php

namespace Celysium\ACL\Controllers;

use Celysium\ACL\Models\Role;
use Celysium\ACL\Traits\AuthorizesUser;
use Celysium\Responser\Responser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct()
    {
    }

    public function roles(Request $request, $id): JsonResponse
    {
        $request->validate([
            'roles' => [
                'required',
                'array',
            ],
            'roles.*' => [
                'integer',
                'exists:roles,id',
            ],
        ]);

        $model = config('acl.user.model');

        if($this->isAuthorizesUser($model)) {
            /** @var AuthorizesUser $user */
            $user = $model->query()->findOrFail($id);
            $user->assignRole($request->get('roles'));

            return Responser::success();
        }

        return Responser::serverError('model user dont use trait AuthorizesUser');
    }

    public function permissions(Request $request, $id): JsonResponse
    {
        $request->validate([
            'permissions' => [
                'required',
                'array',
            ],
            'permissions.*' => [
                'integer',
                'exists:permissions,id',
            ],
            'permissions.*.is_able' => [
                'required',
                'boolean',
            ],
        ]);

        $model = config('acl.user.model');

        if($this->isAuthorizesUser($model)) {
            /** @var AuthorizesUser $user */
            $user = $model->query()->findOrFail($id);
            $user->assignPermissions($request->get('permissions'));


            return Responser::success();
        }

        return Responser::serverError('model user dont use trait AuthorizesUser');
    }

    /**
     * @param string $model
     * @return bool
     */
    protected function isAuthorizesUser(string $model): bool
    {
        return in_array(AuthorizesUser::class, class_uses_recursive($model));
    }
}