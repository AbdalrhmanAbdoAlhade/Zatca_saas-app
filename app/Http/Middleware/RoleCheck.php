<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleCheck
{
    use ApiResponse;

    /**
     * الاستخدام: ->middleware('role:company-owner,accountant')
     * أو مختلط مع صلاحيات: ->middleware('role:company-owner,invoices.create')
     *
     * أي عنصر فيه نقطة (زي invoices.create) بيتفحص كـ صلاحية (permission)
     * بتاعة دور اليوزر - ده اللي بيخلي الأدوار المخصصة (اللي company-owner
     * أو super-admin عملوها بصلاحيات محددة) تشتغل فعلياً على المسارات دي،
     * مش بس أدوار النظام الثابتة زي قبل.
     */
    public function handle(Request $request, Closure $next, string ...$allowed): Response
    {
        $user = $request->user();

        if (! $user || ! $user->role) {
            return $this->error(__('messages.action_not_allowed'), 403);
        }

        foreach ($allowed as $item) {
            if (str_contains($item, '.')) {
                if ($user->hasPermission($item)) {
                    return $next($request);
                }

                continue;
            }

            if ($user->role->slug === $item) {
                return $next($request);
            }
        }

        return $this->error(__('messages.action_not_allowed'), 403);
    }
}
