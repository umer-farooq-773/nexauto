<?php

namespace App\Http\Controllers;

use App\Dto\DriverMateDto;
use App\Interfaces\DriverMateInterface;
use App\Http\Requests\DriverMateRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DriverMateController extends BaseController
{
    public function __construct(DriverMateInterface $repo)
    {
        $this->setRepo($repo, 'auth.pages.driver-mate', 'driver_mate');
    }

    public function show($id)
    {
        $data = $this->_repo->show($id);
        if ($data == null) {
            abort(404);
        }

        // Get all drivers
        $allDrivers = \App\Models\DriverDetail::all();
        
        // Get matched driver IDs
        $matchedDriverIds = $data->drivers->pluck('id')->toArray();
        
        // Get unmatched drivers
        $unmatchedDrivers = $allDrivers->whereNotIn('id', $matchedDriverIds);
        
        // Matched drivers
        $matchedDrivers = $data->drivers;

        return view($this->_directory . '.show', compact('data', 'matchedDrivers', 'unmatchedDrivers'));
    }

    public function index()
    {
        $data['all'] = $this->_repo->index();

        $totalMates = $this->_repo->all();
        $data['all_mate_count']    = count($totalMates);
        $data['active_count']      = count($totalMates->where('status', '1'));
        $data['inactive_count']    = count($totalMates->where('status', '0'));

        return view($this->_directory . '.all', compact('data'));
    }

    public function store(DriverMateRequest $request)
    {
        try {
            $this->_repo->store(DriverMateDto::fromRequest($request->validated()));
            return redirect()->route($this->_route . '.index')->with('success', __('language.successfully_created'));
        } catch (\Throwable $th) {
            return redirect()->route($this->_route . '.index')->with('error', __('language.something_went_wrong'));
        }
    }

    public function update(DriverMateRequest $request, $id)
    {
        try {
            $this->_repo->update($id, DriverMateDto::fromRequest($request->validated()));
            return redirect()->route($this->_route . '.index')->with('success', __('language.updated_successfully'));
        } catch (\Throwable $th) {
            if ($th instanceof NotFoundHttpException) {
                return redirect()->route($this->_route . '.index')->with('error', $th->getMessage());
            }
            return redirect()->route($this->_route . '.index')->with('error', __('language.something_went_wrong'));
        }
    }
}
