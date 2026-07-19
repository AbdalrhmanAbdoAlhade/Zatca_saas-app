<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as RequestFacade;

class ActivityLogService
{
    public function log(
        string $action,
        string $module,
        ?object $subject = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $companyId = null,
    ): ActivityLog {
        return ActivityLog::create([
            'company_id' => $companyId ?? Auth::user()?->company_id,
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'subject_id' => $subject?->id,
            'subject_type' => $subject ? get_class($subject) : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => RequestFacade::ip(),
            'user_agent' => RequestFacade::userAgent(),
            'created_at' => now(),
        ]);
    }

    public function list(array $filters = []): LengthAwarePaginator
    {
        return ActivityLog::query()
            ->with('user:id,name,email')
            ->when($filters['module'] ?? null, fn ($q, $module) => $q->where('module', $module))
            ->when($filters['user_id'] ?? null, fn ($q, $userId) => $q->where('user_id', $userId))
            ->when($filters['company_id'] ?? null, fn ($q, $companyId) => $q->where('company_id', $companyId))
            ->latest('created_at')
            ->paginate($filters['per_page'] ?? 30);
    }
}
