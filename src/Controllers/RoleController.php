<?php

namespace Celysium\Permission\Controllers;

use Celysium\Base\Controller\Controller;
use Celysium\Permission\Models\Permission;
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
     * @param Role $role
     * @param callable|null $authorize
     * @return Model|JsonResponse
     */
    public function show(Role $role, callable $authorize = null): Model|JsonResponse
    {
        if ($authorize) {
            $authorize();
        }

        return $role;
    }

    /**
     * @param Request $request
     * @param callable|null $authorize
     * @return Role|JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request, callable $authorize = null): Role|JsonResponse
    {
        if ($authorize) {
            $authorize();
        }

        $this->validate($request, [
            'name'                 => ['required', 'string', 'max:193', 'unique:roles,name'],
            'title'                => ['required', 'string', 'max:193', 'unique:roles,title'],
            'status'               => ['required', 'boolean'],
            'permissions'          => ['nullable', 'array'],
            'permissions.all'      => ['nullable', 'array'],
            'permissions.only'     => ['nullable', 'array'],
            'permissions.only.*'   => ['integer', 'exists:permissions,id'],
            'permissions.except'   => ['nullable', 'array'],
            'permissions.except.*' => ['integer', 'exists:permissions,id'],
            'permissions.append'   => ['nullable', 'array'],
            'permissions.append.*' => ['integer', 'exists:permissions,id']
        ]);

        DB::beginTransaction();

        /** @var Role $role */
        $role = $this->repository->store($request->except('permissions'));

        $role = $this->assignPermissions($request, $role);

        DB::commit();

        return $role;
    }

    /**
     * @param Request $request
     * @param Role $role
     * @param callable|null $authorize
     * @return Role|JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, Role $role, callable $authorize = null): Role|JsonResponse
    {
        if ($authorize) {
            $authorize();
        }

        $this->validate($request, [
            'name'                 => ['required', 'string', 'max:193', 'unique:roles,name,' . $role->id],
            'title'                => ['required', 'string', 'max:193', 'unique:roles,title,' . $role->id],
            'status'               => ['nullable', 'boolean'],
            'permissions'          => ['nullable', 'array'],
            'permissions.all'      => ['nullable', 'array'],
            'permissions.only'     => ['nullable', 'array'],
            'permissions.only.*'   => ['integer', 'exists:permissions,id'],
            'permissions.except'   => ['nullable', 'array'],
            'permissions.except.*' => ['integer', 'exists:permissions,id'],
            'permissions.append'   => ['nullable', 'array'],
            'permissions.append.*' => ['integer', 'exists:permissions,id']
        ]);

        DB::beginTransaction();

        /** @var Role $role */
        $role = $this->repository->update($role, $request->except('permissions'));

        $role = $this->assignPermissions($request, $role);

        DB::commit();

        return $role;
    }

    /**
     * @param Role $role
     * @param callable|null $authorize
     * @return bool|JsonResponse
     * @throws ValidationException
     */
    public function destroy(Role $role, callable $authorize = null): bool|JsonResponse
    {
        if ($authorize) {
            $authorize();
        }

        if ($role->permissions()->count() || $role->users()->count()) {
            throw ValidationException::withMessages([
                'id' => [__('permission::messages.role_cannot_delete')]
            ]);
        }

        return $this->repository->destroy($role);
    }

    /**
     * @param Role $role
     * @param Request $request
     * @param callable|null $authorize
     * @return Role|JsonResponse
     * @throws ValidationException
     */
    public function syncPermissions(Role $role, Request $request, callable $authorize = null): Role|JsonResponse
    {
        if ($authorize) {
            $authorize();
        }

        $this->validate($request, [
            'permissions'          => ['required', 'array'],
            'permissions.all'      => ['nullable', 'array'],
            'permissions.only'     => ['nullable', 'array'],
            'permissions.only.*'   => ['integer', 'exists:permissions,id'],
            'permissions.except'   => ['nullable', 'array'],
            'permissions.except.*' => ['integer', 'exists:permissions,id'],
            'permissions.append'   => ['nullable', 'array'],
            'permissions.append.*' => ['integer', 'exists:permissions,id']
        ]);

        $role = $this->assignPermissions($request, $role);

        return $role->refresh();
    }

    /**
     * @param Request $request
     * @param Role $role
     * @return Role
     */
    public function assignPermissions(Request $request, Role $role): Role
    {
        if ($request->has('permissions.all')) {
            $permissions = Permission::query()->pluck('id')->toArray();

            $role->permissions()->sync($permissions);
        }

        if ($request->has('permissions.only')) {
            $permissions = $request->input('permissions.only');

            $role->permissions()->sync($permissions);
        }

        if ($request->has('permissions.except')) {
            $permissions = $request->input('permissions.except');

            $role->permissions()->detach($permissions);
        }

        if ($request->has('permissions.append')) {
            $permissions = $request->input('permissions.append');

            $role->permissions()->syncWithoutDetaching($permissions);
        }

        return $role;
    }
}