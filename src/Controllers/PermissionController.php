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
     * @param callable|null $authroize
     * @return LengthAwarePaginator|Collection|JsonResponse
     */
    public function index(Request $request, callable $authroize = null): LengthAwarePaginator|Collection|JsonResponse
    {
        if ($authroize) {
            $authroize();
        }

        return $this->repository->index($request->all());
    }

    /**
     * @param int $id
     * @param callable|null $authroize
     * @return Model|JsonResponse
     */
    public function show(int $id, callable $authroize = null): Model|JsonResponse
    {
        if ($authroize) {
            $authroize();
        }

        return $this->repository->findOrFail($id);
    }

    /**
     * @param Request $request
     * @param callable|null $authroize
     * @return Permission|JsonResponse
     */
    public function store(Request $request, callable $authroize = null): Permission|JsonResponse
    {
        if ($authroize) {
            $authroize();
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
     * @param callable|null $authroize
     * @return Permission|JsonResponse
     */
    public function update(Request $request, int $id, callable $authroize = null): Permission|JsonResponse
    {
        if ($authroize) {
            $authroize();
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
     * @param callable|null $authroize
     * @return bool|JsonResponse
     * @throws ValidationException
     */
    public function destroy(int $id, callable $authroize = null): bool|JsonResponse
    {
        if ($authroize) {
            $authroize();
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