<?php
namespace App\Http\Controllers;

use App\Dto\DriverDetailDto;
use App\Interfaces\DriverDetailInterface;
use App\Http\Requests\DriverDetailRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DriverDetailController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return $reauest, $modal
     */
    public function __construct(DriverDetailInterface $repo)
    {
        $this->setRepo($repo, 'auth.pages.driver', 'driver');
    }

    public function index()
    {
        $data['all'] = $this->_repo->index()->load('vehicles');

        $totalDrivers = $this->_repo->all();
        $data['all_driver_count'] = count($totalDrivers);
        $data['on_duty_count'] = count($totalDrivers->where('status', '1'));
        $data['ratting_count'] = count($totalDrivers->where('status', '2'));
        $data['off_duty_count'] = count($totalDrivers->where('status', '0'));

        return view($this->_directory . '.all', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = $this->_repo->show($id);
        if (!$data) abort(404);
        $data->load('vehicles');
        return view($this->_directory . '.show', compact('data'));
    }

    public function edit($id)
    {
        $data = $this->_repo->show($id);
        if (!$data) abort(404);
        $data->load('vehicles');
        return view($this->_directory . '.edit', compact('data'));
    }

    public function store(DriverDetailRequest $request)
    {
        try {
            $this->_repo->store(DriverDetailDto::fromRequest($request->validated()));
            return redirect()->route($this->_route . '.index')->with('success', __('language.successfully_created'));
        } catch (\Throwable $th) {
            \Log::error('Driver Store Error: ' . $th->getMessage(), ['exception' => $th]);
            $errorMessage = config('app.debug') ? $th->getMessage() : __('language.something_went_wrong');
            return redirect()->back()->withInput()->with('error', $errorMessage);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request Validation $validation
     * @return \Illuminate\Http\Response
     */
    public function update(DriverDetailRequest $request, $id)
    {
        try {
            $this->_repo->update($id, DriverDetailDto::fromRequest($request->validated()));
            return redirect()->route($this->_route . '.index')->with('success', __('language.updated_successfully'));
        } catch (\Throwable $th) {
            \Log::error('Driver Update Error: ' . $th->getMessage(), ['exception' => $th]);
            if ($th instanceof NotFoundHttpException) {
                $message = $th->getMessage();
                return redirect()->route($this->_route . '.index')->with('error', $message);
            } else {
                $errorMessage = config('app.debug') ? $th->getMessage() : __('language.something_went_wrong');
                return redirect()->back()->withInput()->with('error', $errorMessage);
            }
        }
    }
}
