<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserService
{
    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    public function list(array $filters = []): LengthAwarePaginator
    {
        return User::query()
            ->with('role')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->when($filters['role_id'] ?? null, fn ($q, $roleId) => $q->where('role_id', $roleId))
            ->latest()
            ->paginate($filters['per_page'] ?? 20);
    }

    public function create(array $data): User
    {
        $data['company_id'] = Auth::user()->company_id;
        $data['password'] = Hash::make($data['password']);
        $data['status'] = 'active';

        $user = User::create($data);

        // نزود عداد الاستخدام في الاشتراك النشط - يخدم SubscriptionLimit middleware
        Auth::user()->company?->activeSubscription?->increment('users_used');

        $this->activityLog->log('created', 'users', $user, null, ['name' => $user->name, 'email' => $user->email]);

        return $user->load('role');
    }

    public function update(User $user, array $data): User
    {
        // منع أي حد يشيل نفسه من دوره أو يوقف حسابه بالغلط
        if ($user->id === Auth::id() && (isset($data['role_id']) || isset($data['status']))) {
            throw new RuntimeException('cannot_modify_own_role_or_status');
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        $this->activityLog->log('updated', 'users', $user, null, array_diff_key($data, ['password' => true]));

        return $user->fresh('role');
    }

    public function delete(User $user): void
    {
        if ($user->id === Auth::id()) {
            throw new RuntimeException('cannot_delete_own_account');
        }

        if ($user->hasRole('company-owner')) {
            throw new RuntimeException('cannot_delete_company_owner');
        }

        $this->activityLog->log('deleted', 'users', $user, ['name' => $user->name, 'email' => $user->email], null);

        $user->delete();
    }
}
