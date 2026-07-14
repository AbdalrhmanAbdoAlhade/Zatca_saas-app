<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Http\Requests\Company\UpdateCompanySettingRequest;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\CompanySettingResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    use ApiResponse;

    public function show(Request $request)
    {
        return $this->success(new CompanyResource($request->user()->company));
    }

    public function update(UpdateCompanyRequest $request)
    {
        $company = $request->user()->company;
        $company->update($request->validated());

        return $this->success(new CompanyResource($company->fresh()), __('messages.updated_successfully'));
    }

    public function showSettings(Request $request)
    {
        return $this->success(new CompanySettingResource($request->user()->company->settings));
    }

    public function updateSettings(UpdateCompanySettingRequest $request)
    {
        $settings = $request->user()->company->settings;
        $settings->update($request->validated());

        return $this->success(new CompanySettingResource($settings->fresh()), __('messages.updated_successfully'));
    }
}
