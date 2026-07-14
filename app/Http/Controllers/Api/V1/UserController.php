<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use RuntimeException;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(protected UserService $userService)
    {
    }

    public function index(Request $request)
    {
        $users = $this->userService->list($request->only(['search', 'role_id', 'per_page']));

        return $this->success(UserResource::collection($users)->response()->getData(true));
    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->create($request->validated());

        return $this->success(new UserResource($user), __('messages.created_successfully'), 201);
    }

    public function show(User $user)
    {
        return $this->success(new UserResource($user->load('role')));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $user = $this->userService->update($user, $request->validated());
        } catch (RuntimeException $e) {
            return $this->error(__('users.'.$e->getMessage()), 422);
        }

        return $this->success(new UserResource($user), __('messages.updated_successfully'));
    }

    public function destroy(User $user)
    {
        try {
            $this->userService->delete($user);
        } catch (RuntimeException $e) {
            return $this->error(__('users.'.$e->getMessage()), 422);
        }

        return $this->success(null, __('messages.deleted_successfully'));
    }
}
