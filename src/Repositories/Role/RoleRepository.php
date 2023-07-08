<?php

namespace Celysium\Permission\Repositories\Role;

use Celysium\Base\Repository\BaseRepository;
use Celysium\Permission\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    public function __construct(Role $role)
    {
        parent::__construct($role);
    }

    public function conditions(Builder $query): array
    {
        return [
            'status' => '=',
            'name'   => 'like',
            'title'  => 'like',
        ];
    }

    public function store(array $parameters): Model
    {
        DB::beginTransaction();

        /** @var Role $role */
        $role = parent::store($parameters);

        if (isset($parameters['permissions'])) {
            $role->permissions()->sync($parameters['permissions']);
        }
        DB::commit();

        return $role;
    }

    public function update(Model $model, array $parameters): Model
    {
        DB::beginTransaction();

        /** @var Role $role */
        $role = parent::update($model, $parameters);

        if (isset($parameters['permissions'])) {
            $role->permissions()->sync($parameters['permissions']);
        }
        DB::commit();

        return $role;
    }


    /**
     * @param Role $role
     * @param array $parameters
     * @return Role
     */
    public function syncPermissions(Role $role, array $parameters): Role
    {
        $role->permissions()->sync($parameters['permissions']);

        return $role->refresh();
    }
}