<?php

namespace Celysium\Permission\Controllers;

use Celysium\Permission\Models\Permission;
use Celysium\Responser\Responser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function __construct(protected Permission $permission)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $query = $this->permission->query();

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
                'unique:permissions,name'
            ],
            'title' => [
                'required',
                'string',
                'max:193',
                'unique:permissions,title'
            ],
            'roles' => [
                'required',
                'array',
            ],
            'roles.*' => [
                'integer',
                'exists:roles,id',
            ],
        ]);

        DB::beginTransaction();

        /** @var Permission $permission */
        $permission = $this->permission->query()->create($request->all());

        $permission->roles()->sync($request->get('roles'));

        DB::commit();

        return Responser::created($permission->toArray());
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:193',
                'unique:permissions,name,' . $id
            ],
            'title' => [
                'required',
                'string',
                'max:193',
                'unique:permissions,title,' . $id
            ],
            'roles' => [
                'required',
                'array',
            ],
            'roles.*' => [
                'integer',
                'exists:roles,id',
            ],
        ]);

        DB::beginTransaction();

        /** @var Permission $permission */
        $permission = $this->permission->query()->findOrFail($id);

        $permission->update($request->all());

        $permission->roles()->sync($request->get('roles'));

        DB::commit();

        return Responser::success($permission->fresh()->toArray());

    }

    public function destroy(int $id): JsonResponse
    {
        /** @var Permission $permission */
        $permission = $this->permission->query()->findOrFail($id);

        if($permission->roles()->count() || $permission->users()->count()) {
            return Responser::unprocessable([
                'id' => [__('permission::messages.permission_cannot_delete')]
            ]);
        }

        $permission->delete();

        return Responser::deleted();
    }
}