<?php

namespace App\Repositories;

use App\Constants\Constants;
use App\Dto\Permission\RoleDto;
use App\Dto\Permission\RoleUpdateDto;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Cache;

class RoleRepository extends BaseRepository
{
    /**
     * Create a new service instance.
     *
     * @return $reauest, $modal
     */
    public function __construct(Role $model)
    {
        $this->setModel($model);
    }

    public function get_all_roles()
    {
        return $this->_model->where('name', '!=', Constants::SUPERADMIN)->get();
    }

    public function get_staff_roles()
    {
        return $this->_model->whereNotIn('name', [Constants::SUPERADMIN, Constants::USER])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleDto $data)
    {
        $dataArr = $data->toArray();
        $permissions = array_keys(array_filter($dataArr['permissions'] ?? []));

        unset($dataArr['permissions']);

        $role = $this->add($this->_model, $dataArr);
        $role->availablePermissions()->sync($permissions);

        Cache::flush();

        return $role;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, RoleUpdateDto $data)
    {
        $result = $this->checkRecord($id);

        $dataArr = $data->toArray();
        $permissions = array_keys(array_filter($dataArr['permissions'] ?? []));

        unset($dataArr['permissions']);

        $result->update(['title' => $data->title]);
        $result->availablePermissions()->sync($permissions);

        Cache::flush();

        return true;
    }

    public function assign_permission($data)
    {
        $role = $this->checkRecord($data['role']);
        $permissions = array_keys(array_filter($data['permissions'] ?? []));

        $role->syncPermissions($permissions);

        Cache::flush();

        return true;
    }
}
