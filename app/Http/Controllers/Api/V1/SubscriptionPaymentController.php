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

    public function show(SubscriptionPayment $subscriptionPayment)
    {
        return $this->success(new SubscriptionPaymentResource($subscriptionPayment));
    }
}
