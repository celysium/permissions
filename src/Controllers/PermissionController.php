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
     * @param callable|null $authorize
     * @return Permission|JsonResponse
     * @throws ValidationException
     */
    public function store(Request  $request,
                          callable $authorize = null): Permission|JsonResponse
    {
        if ($authorize) {
            $authorize();
        }

        $this->validate($request, [
            'name'    => ['required', 'string', 'max:193', 'unique:permissions,name'],
            'title'   => ['required', 'string', 'max:193', 'unique:permissions,title'],
            'roles'   => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        DB::beginTransaction();

        /** @var Permission $permission */
        $permission = $this->repository->store($request->all());

        if($roles = $request->get('roles')) {
            $permission->roles()->sync($roles);
        }
        DB::commit();

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
            'name'    => ['required', 'string', 'max:193', 'unique:permissions,name,' . $permission->id],
            'title'   => ['required', 'string', 'max:193', 'unique:permissions,title,' . $permission->id],
            'roles'   => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        DB::beginTransaction();

        /** @var Permission $permission */
        $permission = $this->repository->update($permission, $request->all());

        if($roles = $request->get('roles')) {
            $permission->roles()->sync($roles);
        }
        DB::commit();

        return $permission;

    }

    /**
     * @param Permission $permission
     * @param callable|null $authorize
     * @return bool|JsonResponse
     * @throws ValidationException
     */
    public function destroy(Permission $permission, callable $authorize = null): bool|JsonResponse
    {
        if ($authorize) {
            $authorize();
        }

        if ($permission->roles()->count() || $permission->users()->count()) {
            throw ValidationException::withMessages([
                'id' => [__('permission::messages.permission_cannot_delete')]
            ]);
        }

        return $permission->delete();
    }
}