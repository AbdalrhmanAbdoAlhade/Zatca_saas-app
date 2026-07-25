<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionPaymentResource;
use App\Models\SubscriptionPayment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class SubscriptionPaymentController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $payments = SubscriptionPayment::query()
            ->when($request->subscription_id, fn ($q, $id) => $q->where('subscription_id', $id))
            ->latest()
            ->paginate($request->input('per_page', 20));

        return $this->success(SubscriptionPaymentResource::collection($payments)->response()->getData(true));
    }
  public function toggleAutoRenew(Request $request)
{
    $companyId = Auth::user()->company_id;
    
    $subscription = Subscription::where('company_id', $companyId)
        ->where('status', 'active')
        ->firstOrFail();

    $subscription->update([
        'auto_renew' => !$subscription->auto_renew
    ]);

    return response()->json([
        'message' => 'تم تغيير حالة التجديد التلقائي بنجاح',
        'auto_renew' => $subscription->auto_renew
    ]);
}

    public function show(SubscriptionPayment $subscriptionPayment)
    {
        return $this->success(new SubscriptionPaymentResource($subscriptionPayment));
    }
}
