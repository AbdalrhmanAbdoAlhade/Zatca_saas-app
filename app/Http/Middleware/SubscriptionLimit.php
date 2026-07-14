<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionLimit
{
    use ApiResponse;

    /**
     * الاستخدام: ->middleware('subscription.limit:invoices') أو ->middleware('subscription.limit:users')
     */
    public function handle(Request $request, Closure $next, string $resource): Response
    {
        $company = $request->user()?->company;
        $subscription = $company?->activeSubscription;

        if (! $subscription) {
            return $this->error(__('messages.no_active_subscription'), 403);
        }

        if ($resource === 'invoices' && $subscription->invoices_used >= $subscription->invoices_limit) {
            return $this->error(__('messages.invoices_limit_reached'), 403);
        }

        if ($resource === 'users' && $subscription->users_used >= $subscription->users_limit) {
            return $this->error(__('messages.users_limit_reached'), 403);
        }

        return $next($request);
    }
}
