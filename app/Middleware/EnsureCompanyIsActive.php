<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyIsActive
{
    use ApiResponse;

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (! $user->isActive()) {
            return $this->error(__('auth.user_inactive'), 403);
        }

        if (! $user->company || ! $user->company->isActive()) {
            return $this->error(__('auth.company_suspended'), 403);
        }

        return $next($request);
    }
}
