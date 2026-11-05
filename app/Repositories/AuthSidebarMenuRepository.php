<?php

namespace App\Repositories;

use App\Interfaces\AuthSidebarMenuInterface;
use App\Dto\AuthSidebarMenuDto;
use App\Models\AuthSidebarMenu;
use Illuminate\Support\Facades\Cache;

class AuthSidebarMenuRepository extends BaseRepository implements AuthSidebarMenuInterface
{
    /**
     * Create a new service instance.
     *
     * @return $reauest, $modal
     */
    public function __construct(AuthSidebarMenu $model)
    {
        $this->setModel($model);
    }

    public function index($relation = null)
    {
        return $this->_model->whereNull('parent_id')
            ->with([
                'children' => function ($query) {
                    $query
                        ->orderBy('sort_order');
                },
                'parent'
            ])
            ->orderBy('sort_order')
            ->get();
    }

    public function sidebarMenus()
    {
        $user = auth()->user();
        
        if (!$user) {
            return collect();
        }

        // Cache bypass for accurate sidebar rendering during development/debugging.
        // If you want caching back later, remove this bypass and restore Cache::remember().
        $permissions = $user->getAllPermissions()->pluck('name');

        return $this->_model->where('status', 1)
            ->whereNull('parent_id')
            ->orderByRaw('CAST(sort_order AS UNSIGNED) ASC')
            ->with([
                'permissions',
                'children' => function ($query) use ($permissions) {
                    $query->where('status', 1)
                        ->orderByRaw('CAST(sort_order AS UNSIGNED) ASC')
                        ->where(function($q) use ($permissions) {
                            // Show children that either have no permission restrictions
                            // OR have permissions matching user's permissions
                            $q->whereDoesntHave('permissions')
                              ->orWhereHas('permissions', function ($subQ) use ($permissions) {
                                  $subQ->whereIn('name', $permissions);
                              });
                        })
                        ->with('permissions');
                },
                'parent'
            ])
            ->where(function($query) use ($permissions) {
                // Show parent menus that either have no permission restrictions
                // OR have permissions matching user's permissions
                $query->whereDoesntHave('permissions')
                      ->orWhereHas('permissions', function ($q) use ($permissions) {
                          $q->whereIn('name', $permissions);
                      });
            })
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AuthSidebarMenuDto $data)
    {
        $dataArray = $data->toArray();

        $menu = $this->checkRecord($dataArray['menu_id']);

        $status = $dataArray['status'];

        $menu->status = $status;
        $menu->save();

        if ($menu->children->count()) {
            foreach ($menu->children as $child) {
                $child->status = $status;
                $child->save();
            }
        }

        return true;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, AuthSidebarMenuDto $data)
    {
        $result = $this->checkRecord($id);

        $dataArray = $data->toArray();
        return $result->update($dataArray);
    }
}

