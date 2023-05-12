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
     * @param int $id
     * @param callable|null $authorize
     * @return Model|JsonResponse
     */
    public function show(int $id, callable $authorize = null): Model|JsonResponse
    {
        if ($authorize) {
            $authorize();
        }

        return $this->repository->findOrFail($id);
    }

    /**
     * @param Request $request
     * @param callable|null $authorize
     * @return Permission|JsonResponse
     */
    public function store(Request $request, callable $authorize = null): Permission|JsonResponse
    {
        if ($authorize) {
            $authorize();
        }

        $request->validate([
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
     * @param int $id
     * @param callable|null $authorize
     * @return Permission|JsonResponse
     */
    public function update(Request $request, int $id, callable $authorize = null): Permission|JsonResponse
    {
        if ($authorize) {
            $authorize();
        }

        $request->validate([
            'name'    => ['required', 'string', 'max:193', 'unique:permissions,name,' . $id],
            'title'   => ['required', 'string', 'max:193', 'unique:permissions,title,' . $id],
            'roles'   => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        DB::beginTransaction();

        /** @var Permission $permission */
        $permission = $this->repository->updateById($id, $request->all());

        if($roles = $request->get('roles')) {
            $permission->roles()->sync($roles);
        }
        DB::commit();

        return $permission;

    }

    /**
     * @param int $id
     * @param callable|null $authorize
     * @return bool|JsonResponse
     * @throws ValidationException
     */
    public function destroy(int $id, callable $authorize = null): bool|JsonResponse
    {
        if ($authorize) {
            $authorize();
        }
        /** @var Permission $permission */
        $permission = $this->repository->findOrFail($id);

        if ($permission->roles()->count() || $permission->users()->count()) {
            throw ValidationException::withMessages([
                'id' => [__('permission::messages.permission_cannot_delete')]
            ]);
        }

        return $permission->delete();
    }
}