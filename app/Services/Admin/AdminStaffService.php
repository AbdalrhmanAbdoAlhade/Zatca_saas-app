<?php

namespace App\Services\Admin;

use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminStaffService
{
    /** الأدوار المسموح استخدامها لفريق المنصة بس - أي رول تاني (زي accountant) ممنوع هنا */
    protected const PLATFORM_ROLES = ['super-admin', 'platform-support'];

    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    public function list(array $filters = []): LengthAwarePaginator
    {
        return User::withoutGlobalScopes()
            ->whereNull('company_id')
            ->with('role')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->latest()
            ->paginate($filters['per_page'] ?? 20);
    }

    public function create(array $data): User
    {
        $role = Role::whereIn('slug', self::PLATFORM_ROLES)->findOrFail($data['role_id']);

        if (! in_array($role->slug, self::PLATFORM_ROLES, true)) {
            throw new RuntimeException('invalid_platform_role');
        }

        $staff = User::withoutGlobalScopes()->create([
            'company_id' => null,
            'role_id' => $role->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'status' => 'active',
            'preferred_locale' => $data['preferred_locale'] ?? 'ar',
        ])->load('role');

        $this->activityLog->log('created', 'admin.staff', $staff, null, [
            'name' => $staff->name,
            'email' => $staff->email,
            'role' => $role->slug,
        ], companyId: null);

        return $staff;
    }

    public function update(User $staff, array $data, User $actingUser): User
    {
        $this->guardIsPlatformStaff($staff);

        if ($staff->id === $actingUser->id && (isset($data['role_id']) || isset($data['status']))) {
            throw new RuntimeException('cannot_modify_own_role_or_status');
        }

        if (isset($data['role_id'])) {
            $role = Role::find($data['role_id']);
            if (! $role || ! in_array($role->slug, self::PLATFORM_ROLES, true)) {
                throw new RuntimeException('invalid_platform_role');
            }
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $staff->update($data);

        $this->activityLog->log('updated', 'admin.staff', $staff, null, array_diff_key($data, ['password' => true]), companyId: null);

        return $staff->fresh('role');
    }

    public function delete(User $staff, User $actingUser): void
    {
        $this->guardIsPlatformStaff($staff);

        if ($staff->id === $actingUser->id) {
            throw new RuntimeException('cannot_delete_own_account');
        }

        $this->activityLog->log('deleted', 'admin.staff', $staff, ['name' => $staff->name, 'email' => $staff->email], null, companyId: null);

        $staff->delete();
    }

    protected function guardIsPlatformStaff(User $staff): void
    {
        if ($staff->company_id !== null) {
            throw new RuntimeException('not_a_platform_staff_member');
        }
    }
}
