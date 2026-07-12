<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Services\SupplierService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use ApiResponse;

    public function __construct(protected SupplierService $supplierService)
    {
    }

    public function index(Request $request)
    {
        $suppliers = $this->supplierService->list($request->only(['search', 'is_active', 'per_page']));

        return $this->success(SupplierResource::collection($suppliers)->response()->getData(true));
    }

    public function store(StoreSupplierRequest $request)
    {
        $supplier = $this->supplierService->create($request->validated());

        return $this->success(new SupplierResource($supplier), __('messages.created_successfully'), 201);
    }

    public function show(Supplier $supplier)
    {
        return $this->success(new SupplierResource($supplier));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $supplier = $this->supplierService->update($supplier, $request->validated());

        return $this->success(new SupplierResource($supplier), __('messages.updated_successfully'));
    }

    public function destroy(Supplier $supplier)
    {
        $this->supplierService->delete($supplier);

        return $this->success(null, __('messages.deleted_successfully'));
    }
}
