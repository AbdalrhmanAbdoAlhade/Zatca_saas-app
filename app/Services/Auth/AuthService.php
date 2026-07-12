<?php

namespace App\Services\Auth;

use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\CompanyZatcaSetting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    /**
     * تسجيل شركة جديدة + أول يوزر فيها (Company Owner) في عملية واحدة atomic.
     */
    public function registerCompany(array $companyData, array $ownerData, ?string $deviceName, ?string $ip, ?string $userAgent): array
    {
        return DB::transaction(function () use ($companyData, $ownerData, $deviceName, $ip, $userAgent) {
            $company = Company::create(array_merge($companyData, [
                'status' => 'active',
            ]));

            $ownerRole = Role::where('slug', 'company-owner')->firstOrFail();

            $user = User::create([
                'company_id' => $company->id,
                'role_id' => $ownerRole->id,
                'name' => $ownerData['name'],
                'email' => $ownerData['email'],
                'phone' => $ownerData['phone'] ?? null,
                'password' => Hash::make($ownerData['password']),
                'status' => 'active',
            ]);

            $company->update(['owner_user_id' => $user->id]);

            // إعدادات افتراضية للشركة الجديدة
            CompanyZatcaSetting::create([
                'company_id' => $company->id,
                'environment' => 'sandbox',
                'compliance_status' => 'not_started',
                'onboarding_stage' => 0,
            ]);

            CompanySetting::create([
                'company_id' => $company->id,
            ]);

            $token = $this->issueToken($user, $deviceName, $ip, $userAgent);

            return [
                'user' => $user->load('role', 'company'),
                'token' => $token->plainTextToken,
            ];
        });
    }

    /**
     * تسجيل الدخول - بيرفض لو اليوزر أو الشركة موقوفين.
     *
     * @throws AuthenticationException
     */
    public function login(string $email, string $password, ?string $deviceName, ?string $ip, ?string $userAgent): array
    {
        $user = User::with(['role', 'company'])->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new AuthenticationException('invalid_credentials');
        }

        if (! $user->isActive()) {
            throw new AuthenticationException('user_inactive');
        }

        if (! $user->company || ! $user->company->isActive()) {
            throw new AuthenticationException('company_suspended');
        }

        $token = $this->issueToken($user, $deviceName, $ip, $userAgent);

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
        ];
    }

    protected function issueToken(User $user, ?string $deviceName, ?string $ip, ?string $userAgent): NewAccessToken
    {
        $token = $user->createToken($deviceName ?: 'default-device');

        // نسجل معلومات إضافية عن الجهاز/الـ IP على التوكن نفسه (أعمدة أضفناها فوق جدول Sanctum)
        $token->accessToken->forceFill([
            'device_name' => $deviceName,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ])->save();

        return $token;
    }

    public function logout(User $user): void
    {
        /** @var PersonalAccessToken|null $currentToken */
        $currentToken = $user->currentAccessToken();
        $currentToken?->delete();
    }

    public function logoutAllDevices(User $user): void
    {
        $user->tokens()->delete();
    }

    public function listSessions(User $user)
    {
        return $user->tokens()
            ->orderByDesc('last_used_at')
            ->get(['id', 'name', 'device_name', 'ip_address', 'last_used_at', 'created_at']);
    }

    public function revokeSession(User $user, int $tokenId): bool
    {
        return (bool) $user->tokens()->where('id', $tokenId)->delete();
    }
}
