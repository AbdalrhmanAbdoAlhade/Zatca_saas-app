<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Services\CustomerService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use ApiResponse;

    public function __construct(protected CustomerService $customerService)
    {
    }

    public function index(Request $request)
    {
        $customers = $this->customerService->list($request->only(['search', 'is_active', 'per_page']));

        return $this->success(CustomerResource::collection($customers)->response()->getData(true));
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = $this->customerService->create($request->validated());

        return $this->success(new CustomerResource($customer), __('messages.created_successfully'), 201);
    }

    public function show(Customer $customer)
    {
        return $this->success(new CustomerResource($customer));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer = $this->customerService->update($customer, $request->validated());

        return $this->success(new CustomerResource($customer), __('messages.updated_successfully'));
    }

    public function destroy(Customer $customer)
    {
        $this->customerService->delete($customer);

        return $this->success(null, __('messages.deleted_successfully'));
    }
}
