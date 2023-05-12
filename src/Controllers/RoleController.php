<?php

namespace Celysium\Permission\Controllers;

use Celysium\Base\Controller\Controller;
use Celysium\Permission\Models\Role;
use Celysium\Permission\Repositories\Role\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public function __construct(protected RoleRepositoryInterface $repository)
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
     * @return Role|JsonResponse
     */
    public function store(Request $request, callable $authorize = null): Role|JsonResponse
    {
        if ($authorize) {
            $authorize();
        }

        $request->validate([
            'name'          => ['required', 'string', 'max:193', 'unique:roles,name'],
            'title'         => ['required', 'string', 'max:193', 'unique:roles,title'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        DB::beginTransaction();

        /** @var Role $role */
        $role = $this->repository->store($request->all());

        $role->permissions()->sync($request->get('permissions'));

        DB::commit();

        return $role;
    }

    /**
     * @param Request $request
     * @param int $id
     * @param callable|null $authorize
     * @return Role|JsonResponse
     */
    public function update(Request $request, int $id, callable $authorize = null): Role|JsonResponse
    {
        if ($authorize) {
            $authorize();
        }

        $request->validate([
            'name'          => ['required', 'string', 'max:193', 'unique:roles,name,' . $id],
            'title'         => ['required', 'string', 'max:193', 'unique:roles,title,' . $id],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        DB::beginTransaction();

        /** @var Role $role */
        $role = $this->repository->updateById($id, $request->all());

        $role->permissions()->sync($request->get('permissions'));

        DB::commit();

        return $role;
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

        /** @var Role $role */
        $role = $this->repository->findOrFail($id);

        if ($role->permissions()->count() || $role->users()->count()) {
           throw ValidationException::withMessages([
               'id' => [__('permission::messages.role_cannot_delete')]
           ]);
        }

        return $this->repository->destroy($role);
    }
}