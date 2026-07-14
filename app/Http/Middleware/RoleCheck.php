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
     * الاستخدام في الراوت: ->middleware('role:company-owner,accountant')
     */
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->role || ! in_array($user->role->slug, $allowedRoles, true)) {
            return $this->error(__('messages.action_not_allowed'), 403);
        }

        return $next($request);
    }
}
