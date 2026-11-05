<?php

namespace App\Http\Controllers;

use App\Dto\RouteTripDto;
use App\Interfaces\RouteTripInterface;
use App\Http\Requests\RouteTripRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RouteTripController extends BaseController
{
    public function __construct(RouteTripInterface $repo)
    {
        $this->setRepo($repo, 'auth.pages.route-trip', 'route_trip');
    }

    public function index()
    {
        $data['all']            = $this->_repo->index();
        $totalRoutes            = $this->_repo->all();
        $data['total_count']   = count($totalRoutes);
        $data['active_count']  = count($totalRoutes->where('status', '1'));
        $data['inactive_count'] = count($totalRoutes->where('status', '0'));

        return view($this->_directory . '.all', compact('data'));
    }

    public function store(RouteTripRequest $request)
    {
        try {
            $this->_repo->store(RouteTripDto::fromRequest($request->validated()));
            return redirect()->route($this->_route . '.index')->with('success', __('language.successfully_created'));
        } catch (\Throwable $th) {
            return redirect()->route($this->_route . '.index')->with('error', __('language.something_went_wrong'));
        }
    }

    public function update(RouteTripRequest $request, $id)
    {
        try {
            $this->_repo->update($id, RouteTripDto::fromRequest($request->validated()));
            return redirect()->route($this->_route . '.index')->with('success', __('language.updated_successfully'));
        } catch (\Throwable $th) {
            if ($th instanceof NotFoundHttpException) {
                return redirect()->route($this->_route . '.index')->with('error', $th->getMessage());
            }
            return redirect()->route($this->_route . '.index')->with('error', __('language.something_went_wrong'));
        }
    }
}
