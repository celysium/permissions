<?php

namespace Celysium\Permission\Controllers;

use Celysium\Base\Controller\Controller;
use Celysium\Permission\Models\Permission;
use Celysium\Permission\Repositories\Permission\PermissionRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PermissionController extends Controller
{
    public function __construct(protected PermissionRepositoryInterface $repository)
    {
    }

    /**
     * @param Request $request
     * @param callable|null $authorize
     * @return LengthAwarePaginator|Collection|JsonResponse
     */
    public function index(Request $request, callable $authorize = null): LengthAwarePaginator|Collection|JsonResponse
    {
        if ($authorize) {
            $authorize();
        }

        return $this->repository->index($request->all());
    }

    /**
     * @param Permission $permission
     * @param callable|null $authorize
     * @return Model|JsonResponse
     */
    public function show(Permission $permission, callable $authorize = null): Model|JsonResponse
    {
        if ($authorize) {
            $authorize();
        }

        return $permission;
    }

    /**
     * @param Request $request
     * @param Permission $permission
     * @param callable|null $authorize
     * @return Permission|JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, Permission $permission, callable $authorize = null): Permission|JsonResponse
    {
        if ($authorize) {
            $authorize();
        }

        $this->validate($request, [

            'service'        => ['required', 'string', 'max:193'],
            'name'    => ['required', 'string', 'max:193'],
            'title'   => ['required', 'string', 'max:193'],
            'routes'         => ['required', 'string', 'max:193', 'unique:permissions,title'],
            'routes.url'     => ['required', 'string'],
            'routes.methods' => ['required', 'array'],
            'roles'   => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        DB::beginTransaction();

        /** @var Permission $permission */
        $permission = $this->repository->update($permission, $request->all());

        if ($roles = $request->get('roles')) {
            $permission->roles()->sync($roles);
        }
        DB::commit();

        return $permission;

    }
}