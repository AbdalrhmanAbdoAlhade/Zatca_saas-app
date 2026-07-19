<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminStaffRequest;
use App\Http\Requests\Admin\UpdateAdminStaffRequest;
use App\Http\Resources\Admin\AdminStaffResource;
use App\Models\User;
use App\Services\Admin\AdminStaffService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AdminStaffController extends Controller
{
    use ApiResponse;

    public function __construct(protected AdminStaffService $staffService)
    {
    }

    public function index(Request $request)
    {
        $staff = $this->staffService->list($request->only(['search', 'per_page']));

        return $this->success(AdminStaffResource::collection($staff)->response()->getData(true));
    }

    public function store(StoreAdminStaffRequest $request)
    {
        try {
            $staff = $this->staffService->create($request->validated());
        } catch (RuntimeException $e) {
            return $this->error(__('admin.'.$e->getMessage()), 422);
        }

        return $this->success(new AdminStaffResource($staff), __('messages.created_successfully'), 201);
    }

    public function update(UpdateAdminStaffRequest $request, User $staff)
    {
        try {
            $staff = $this->staffService->update($staff, $request->validated(), $request->user());
        } catch (RuntimeException $e) {
            return $this->error(__('admin.'.$e->getMessage()), 422);
        }

        return $this->success(new AdminStaffResource($staff), __('messages.updated_successfully'));
    }

    public function destroy(Request $request, User $staff)
    {
        try {
            $this->staffService->delete($staff, $request->user());
        } catch (RuntimeException $e) {
            return $this->error(__('admin.'.$e->getMessage()), 422);
        }

        return $this->success(null, __('messages.deleted_successfully'));
    }
}
