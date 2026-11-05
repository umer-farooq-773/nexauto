<?php

namespace App\Http\Controllers;

use App\Dto\LicenseCategoryDto;
use App\Interfaces\LicenseCategoryInterface;
use App\Http\Requests\LicenseCategoryRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LicenseCategoryController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return $reauest, $modal
     */
    public function __construct(LicenseCategoryInterface $repo)
    {
        $this->setRepo($repo, '{{directory_name}}', 'licensecategories');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(LicenseCategoryRequest $request)
    {
        try {
            $this->_repo->store(LicenseCategoryDto::fromRequest($request->validated()));
            return redirect()->route($this->_route . '.index')->with('success', __('language.successfully_created'));
        } catch (\Throwable $th) {
            return redirect()->route($this->_route . '.index')->with('error', __('language.something_went_wrong'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request Validation $validation
     * @return \Illuminate\Http\Response
     */
    public function update(LicenseCategoryRequest $request, $id)
    {
        try {
            $this->_repo->update($id, LicenseCategoryDto::fromRequest($request->validated()));
            return redirect()->route($this->_route . '.index')->with('success', __('language.updated_successfully'));
        } catch (\Throwable $th) {
            if ($th instanceof NotFoundHttpException) {
                $message = $th->getMessage(); // Get the exception message
                return redirect()->route($this->_route . '.index')->with('error', $message);
            } else {
                return redirect()->route($this->_route . '.index')->with('error', __('language.something_went_wrong'));
            }
        }
    }
}
