<?php

namespace App\Services\Admin;

use App\Models\Plan;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Collection;

class PlanManagementService
{
    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    public function list(): Collection
    {
        return Plan::withCount('subscriptions')->latest()->get();
    }

    public function create(array $data): Plan
    {
        $plan = Plan::create($data);

        $this->activityLog->log('created', 'admin.plans', $plan, null, $data, companyId: null);

        return $plan;
    }

    public function update(Plan $plan, array $data): Plan
    {
        $old = $plan->only(array_keys($data));

        $plan->update($data);

        $this->activityLog->log('updated', 'admin.plans', $plan, $old, $data, companyId: null);

        return $plan->fresh();
    }

    public function delete(Plan $plan): void
    {
        // الـ FK بتاع subscriptions.plan_id هو restrictOnDelete أصلاً، فأي اشتراك
        // مرتبط (حتى لو ملغي/منتهي) هيمنع الحذف على مستوى الداتابيز كمان.
        if ($plan->subscriptions()->exists()) {
            throw new \RuntimeException('plan_has_subscriptions');
        }

        $this->activityLog->log('deleted', 'admin.plans', $plan, ['slug' => $plan->slug], null, companyId: null);

        $plan->delete();
    }
}
