<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(protected ProductService $productService)
    {
    }

    public function index(Request $request)
    {
        $products = $this->productService->list($request->only(['search', 'is_active', 'per_page']));

        return $this->success(ProductResource::collection($products)->response()->getData(true));
    }

    public function store(StoreProductRequest $request)
    {
        $product = $this->productService->create($request->validated());

        return $this->success(new ProductResource($product), __('messages.created_successfully'), 201);
    }

    public function show(Product $product)
    {
        return $this->success(new ProductResource($product));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product = $this->productService->update($product, $request->validated());

        return $this->success(new ProductResource($product), __('messages.updated_successfully'));
    }

    public function destroy(Product $product)
    {
        $this->productService->delete($product);

        return $this->success(null, __('messages.deleted_successfully'));
    }
}
