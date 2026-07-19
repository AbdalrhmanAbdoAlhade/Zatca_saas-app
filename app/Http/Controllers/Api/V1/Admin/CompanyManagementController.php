<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminCompanyRequest;
use App\Http\Requests\Admin\StoreCompanySubscriptionRequest;
use App\Http\Resources\Admin\AdminCompanyResource;
use App\Models\Company;
use App\Services\Admin\PlatformAdminService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CompanyManagementController extends Controller
{
    use ApiResponse;

    public function __construct(protected PlatformAdminService $adminService)
    {
    }

    public function index(Request $request)
    {
        $companies = $this->adminService->listCompanies($request->only(['search', 'status', 'per_page']));

        return $this->success(AdminCompanyResource::collection($companies)->response()->getData(true));
    }

    public function show(int $id)
    {
        $company = $this->adminService->getCompany($id);

        return $this->success(new AdminCompanyResource($company));
    }

    public function store(StoreAdminCompanyRequest $request)
    {
        $company = $this->adminService->createCompany(
            $request->validated('company'),
            $request->validated('owner'),
            $request->validated('plan_id'),
            $request->user(),
        );

        return $this->success(new AdminCompanyResource($company), __('messages.created_successfully'), 201);
    }

    public function destroy(Request $request, Company $company)
    {
        $this->adminService->deleteCompany($company, $request->user());

        return $this->success(null, __('messages.deleted_successfully'));
    }

    public function suspend(Request $request, Company $company)
    {
        $company = $this->adminService->suspendCompany($company, $request->user());

        return $this->success(new AdminCompanyResource($company), __('admin.company_suspended'));
    }

    public function activate(Request $request, Company $company)
    {
        $company = $this->adminService->activateCompany($company, $request->user());

        return $this->success(new AdminCompanyResource($company), __('admin.company_activated'));
    }

    public function activateSubscription(StoreCompanySubscriptionRequest $request, Company $company)
    {
        $subscription = $this->adminService->activateSubscription($company, $request->validated(), $request->user());

        return $this->success($subscription, __('admin.subscription_activated'), 201);
    }

    public function stats()
    {
        return $this->success($this->adminService->stats());
    }
}
