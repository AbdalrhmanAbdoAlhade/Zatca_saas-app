<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Services\RoleManagementService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use RuntimeException;

class RoleController extends Controller
{
    use ApiResponse;

    public function __construct(protected RoleManagementService $roleService)
    {
    }

    public function index(Request $request)
    {
        return $this->success(RoleResource::collection($this->roleService->listFor($request->user())));
    }

    public function permissionsCatalog()
    {
        return $this->success($this->roleService->listPermissions());
    }

    public function store(StoreRoleRequest $request)
    {
        try {
            $role = $this->roleService->create($request->validated(), $request->user());
        } catch (RuntimeException $e) {
            return $this->error(__('roles.'.$e->getMessage()), 422);
        }

        return $this->success(new RoleResource($role), __('messages.created_successfully'), 201);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        try {
            $role = $this->roleService->update($role, $request->validated(), $request->user());
        } catch (RuntimeException $e) {
            return $this->error(__('roles.'.$e->getMessage()), 422);
        }

        return $this->success(new RoleResource($role), __('messages.updated_successfully'));
    }

    public function destroy(Request $request, Role $role)
    {
        try {
            $this->roleService->delete($role, $request->user());
        } catch (RuntimeException $e) {
            return $this->error(__('roles.'.$e->getMessage()), 422);
        }

        return $this->success(null, __('messages.deleted_successfully'));
    }
}
