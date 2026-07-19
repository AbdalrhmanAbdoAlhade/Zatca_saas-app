<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Http\Resources\Admin\PlanResource;
use App\Models\Plan;
use App\Services\Admin\PlanManagementService;
use App\Traits\ApiResponse;
use RuntimeException;

class PlanController extends Controller
{
    use ApiResponse;

    public function __construct(protected PlanManagementService $planService)
    {
    }

    public function index()
    {
        return $this->success(PlanResource::collection($this->planService->list()));
    }

    public function store(StorePlanRequest $request)
    {
        $plan = $this->planService->create($request->validated());

        return $this->success(new PlanResource($plan), __('messages.created_successfully'), 201);
    }

    public function update(UpdatePlanRequest $request, Plan $plan)
    {
        $plan = $this->planService->update($plan, $request->validated());

        return $this->success(new PlanResource($plan), __('messages.updated_successfully'));
    }

    public function destroy(Plan $plan)
    {
        try {
            $this->planService->delete($plan);
        } catch (RuntimeException $e) {
            return $this->error(__('admin.'.$e->getMessage()), 422);
        }

        return $this->success(null, __('messages.deleted_successfully'));
    }
}
