<?php

namespace Celysium\ACL\Controllers;

use Celysium\ACL\Models\Role;
use Celysium\Responser\Responser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function __construct(protected Role $role)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $query = $this->role->query();

        if ($title = $request->get('title')) {
            $query->where('title', 'like', "%$title%");
        }
        if ($name = $request->get('name')) {
            $query->where('name', 'like', "%$name%");
        }
        if (! $request->has('paginate') || $request->get('paginate') === false) {
            return Responser::info($query->get());
        } else {
            return Responser::collection($query->paginate());
        }
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:193',
                'unique:roles,name'
            ],
            'title' => [
                'required',
                'string',
                'max:193',
                'unique:roles,title'
            ],
            'permissions' => [
                'required',
                'array',
            ],
            'permissions.*' => [
                'integer',
                'exists:permissions,id',
            ],
        ]);

        DB::beginTransaction();

        /** @var Role $role */
        $role = $this->role->query()->create($request->all());

        $role->permissions()->sync($request->get('permissions'));

        DB::commit();

        return Responser::created($role->toArray());
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:193',
                'unique:roles,name,' . $id
            ],
            'title' => [
                'required',
                'string',
                'max:193',
                'unique:roles,title,' . $id
            ],
            'permissions' => [
                'required',
                'array',
            ],
            'permissions.*' => [
                'integer',
                'exists:permissions,id',
            ],
        ]);

        DB::beginTransaction();

        /** @var Role $role */
        $role = $this->role->query()->findOrFail($id);

        $role->update($request->all());

        $role->permissions()->sync($request->get('permissions'));

        DB::commit();

        return Responser::success($role->fresh()->toArray());

    }

    public function destroy(int $id): JsonResponse
    {
        /** @var Role $role */
        $role = $this->role->query()->findOrFail($id);

        if($role->permissions()->count()) {
            return Responser::unprocessable([
                'id' => [__('acl::messages.role_cannot_delete')]
            ]);
        }

        $role->delete();

        return Responser::deleted();
    }
}