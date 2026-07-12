<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterCompanyRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(protected AuthService $authService)
    {
    }

    public function register(RegisterCompanyRequest $request)
    {
        $result = $this->authService->registerCompany(
            companyData: $request->validated('company'),
            ownerData: $request->validated('owner'),
            deviceName: $request->input('device_name'),
            ip: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return $this->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ], __('auth.registered_successfully'), 201);
    }

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->login(
                email: $request->validated('email'),
                password: $request->validated('password'),
                deviceName: $request->input('device_name'),
                ip: $request->ip(),
                userAgent: $request->userAgent(),
            );
        } catch (AuthenticationException $e) {
            return $this->error(__('auth.'.$e->getMessage()), 401);
        }

        return $this->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ], __('auth.logged_in_successfully'));
    }

    public function me(Request $request)
    {
        return $this->success(
            new UserResource($request->user()->load('role', 'company')),
        );
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return $this->success(null, __('auth.logged_out_successfully'));
    }

    public function logoutAllDevices(Request $request)
    {
        $this->authService->logoutAllDevices($request->user());

        return $this->success(null, __('auth.logged_out_all_devices'));
    }

    public function sessions(Request $request)
    {
        return $this->success(
            $this->authService->listSessions($request->user()),
        );
    }

    public function revokeSession(Request $request, int $tokenId)
    {
        $revoked = $this->authService->revokeSession($request->user(), $tokenId);

        if (! $revoked) {
            return $this->error(__('auth.session_not_found'), 404);
        }

        return $this->success(null, __('auth.session_revoked'));
    }
}
