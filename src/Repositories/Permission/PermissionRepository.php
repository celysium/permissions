<?php

namespace Celysium\Permission\Repositories\Permission;

use Celysium\Base\Repository\BaseRepository;
use Celysium\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PermissionRepository extends BaseRepository implements PermissionRepositoryInterface
{
    public function __construct(Permission $permission)
    {
        parent::__construct($permission);
    }

    public function conditions(Builder $query): array
    {
        return [
            'name' => fn($value) => $query->where('name', 'like', "%$value%"),
            'title' => fn($value) => $query->where('title', 'like', "%$value%"),
        ];
    }

    public function store(array $parameters): Model
    {
        DB::beginTransaction();

        /** @var Permission $permission */
        $permission = parent::store($parameters);

        if (isset($parameters['roles'])) {
            $permission->roles()->sync($parameters['roles']);
        }
        DB::commit();

        return $permission;
    }

    public function update(Model $model, array $parameters): Model
    {
        DB::beginTransaction();

        /** @var Permission $permission */
        $permission = parent::update($model, $parameters);

        if (isset($parameters['roles'])) {
            $permission->roles()->sync($parameters['roles']);
        }
        DB::commit();

        return $permission;
    }
}