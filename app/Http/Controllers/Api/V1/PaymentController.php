<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponse;

    public function __construct(protected PaymentService $paymentService)
    {
    }

    public function index(Request $request)
    {
        $payments = $this->paymentService->list($request->only(['invoice_id', 'per_page']));

        return $this->success(PaymentResource::collection($payments)->response()->getData(true));
    }

    public function store(StorePaymentRequest $request)
    {
        $payment = $this->paymentService->create($request->validated());

        return $this->success(new PaymentResource($payment), __('messages.created_successfully'), 201);
    }

    public function show(Payment $payment)
    {
        return $this->success(new PaymentResource($payment));
    }
}
