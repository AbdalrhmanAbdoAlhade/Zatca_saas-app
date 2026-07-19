<?php

namespace App\Services\Admin;

use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\CompanyZatcaSetting;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PlatformAdminService
{
    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    public function listCompanies(array $filters = []): LengthAwarePaginator
    {
        return Company::query()
            ->withCount('users')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('trade_name_ar', 'like', "%{$search}%")
                ->orWhere('trade_name_en', 'like', "%{$search}%")
                ->orWhere('vat_number', 'like', "%{$search}%"))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate($filters['per_page'] ?? 20);
    }

    public function getCompany(int $id): Company
    {
        return Company::with(['zatcaSettings', 'settings', 'activeSubscription.plan'])
            ->withCount(['users'])
            ->findOrFail($id);
    }

    /**
     * إنشاء شركة جديدة من لوحة الأدمن + أول يوزر فيها (Company Owner) + الإعدادات
     * الافتراضية + اشتراك (بالخطة المحددة، أو "free" لو مفيش خطة متبعتة).
     */
    public function createCompany(array $companyData, array $ownerData, ?int $planId, User $actingAdmin): Company
    {
        return DB::transaction(function () use ($companyData, $ownerData, $planId, $actingAdmin) {
            $company = Company::create(array_merge($companyData, ['status' => 'active']));

            $ownerRole = Role::where('slug', 'company-owner')->firstOrFail();

            $owner = User::withoutGlobalScopes()->create([
                'company_id' => $company->id,
                'role_id' => $ownerRole->id,
                'name' => $ownerData['name'],
                'email' => $ownerData['email'],
                'phone' => $ownerData['phone'] ?? null,
                'password' => Hash::make($ownerData['password']),
                'status' => 'active',
            ]);

            $company->update(['owner_user_id' => $owner->id]);

            CompanyZatcaSetting::create([
                'company_id' => $company->id,
                'environment' => 'sandbox',
                'compliance_status' => 'not_started',
                'onboarding_stage' => 0,
            ]);

            CompanySetting::create(['company_id' => $company->id]);

            $plan = $planId
                ? Plan::find($planId)
                : Plan::where('slug', 'free')->first();

            if ($plan) {
                Subscription::create([
                    'company_id' => $company->id,
                    'plan_id' => $plan->id,
                    'start_date' => now(),
                    'end_date' => now()->addYear(),
                    'invoices_limit' => $plan->max_invoices,
                    'users_limit' => $plan->max_users,
                    'auto_renew' => true,
                    'status' => 'active',
                ]);
            }

            $this->activityLog->log('created', 'admin.companies', $company, null, [
                'trade_name_ar' => $company->trade_name_ar,
                'vat_number' => $company->vat_number,
                'created_by_admin' => $actingAdmin->id,
            ], companyId: $company->id);

            return $company->fresh(['zatcaSettings', 'settings', 'activeSubscription.plan']);
        });
    }

    /**
     * حذف الشركة (soft delete) + إيقافها + إيقاف كل موظفيها، عشان محدش يقدر
     * يدخل حسابها تاني حتى لو الـ FK مسيبتش البيانات المرتبطة تتمسح فوراً.
     */
    public function deleteCompany(Company $company, User $actingAdmin): void
    {
        DB::transaction(function () use ($company, $actingAdmin) {
            User::withoutGlobalScopes()->where('company_id', $company->id)->update(['status' => 'inactive']);
            User::withoutGlobalScopes()->where('company_id', $company->id)->get()->each(
                fn (User $companyUser) => $companyUser->tokens()->delete()
            );

            $company->update(['status' => 'suspended']);
            $company->delete(); // soft delete

            $this->activityLog->log('deleted', 'admin.companies', $company, [
                'trade_name_ar' => $company->trade_name_ar,
            ], null, companyId: $company->id);
        });
    }

    public function suspendCompany(Company $company, User $actingAdmin): Company
    {
        $company->update(['status' => 'suspended']);

        $this->activityLog->log('suspended', 'admin.companies', $company, ['status' => 'active'], ['status' => 'suspended'], companyId: $company->id);

        return $company->fresh();
    }

    public function activateCompany(Company $company, User $actingAdmin): Company
    {
        $company->update(['status' => 'active']);

        $this->activityLog->log('activated', 'admin.companies', $company, ['status' => 'suspended'], ['status' => 'active'], companyId: $company->id);

        return $company->fresh();
    }

    /**
     * تفعيل/إضافة اشتراك جديد لشركة يدوياً (ترقية، تجديد، أو خطة مخصصة).
     * بيوقف أي اشتراك "active" سابق أولاً عشان activeSubscription() تفضل صحيحة.
     */
    public function activateSubscription(Company $company, array $data, User $actingAdmin): Subscription
    {
        return DB::transaction(function () use ($company, $data, $actingAdmin) {
            Subscription::where('company_id', $company->id)
                ->where('status', 'active')
                ->update(['status' => 'cancelled']);

            $plan = Plan::findOrFail($data['plan_id']);

            $subscription = Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'invoices_limit' => $plan->max_invoices,
                'users_limit' => $plan->max_users,
                'auto_renew' => $data['auto_renew'] ?? true,
                'status' => 'active',
            ]);

            $this->activityLog->log('subscription_activated', 'admin.subscriptions', $subscription, null, [
                'company_id' => $company->id,
                'plan' => $plan->slug,
                'activated_by_admin' => $actingAdmin->id,
            ], companyId: $company->id);

            return $subscription->load('plan');
        });
    }

    public function stats(): array
    {
        return [
            'total_companies' => Company::count(),
            'active_companies' => Company::where('status', 'active')->count(),
            'suspended_companies' => Company::where('status', 'suspended')->count(),
            'total_invoices' => Invoice::withoutGlobalScopes()->count(),
            'invoices_this_month' => Invoice::withoutGlobalScopes()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
        ];
    }
}
