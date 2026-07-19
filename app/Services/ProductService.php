<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ProductService
{
    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    public function list(array $filters = []): LengthAwarePaginator
    {
        return Product::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name_ar', 'like', "%{$search}%")
                ->orWhere('name_en', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%"))
            ->when(array_key_exists('is_active', $filters), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->latest()
            ->paginate($filters['per_page'] ?? 20);
    }

    public function create(array $data): Product
    {
        $data['company_id'] = Auth::user()->company_id;

        $product = Product::create($data);

        $this->activityLog->log('created', 'products', $product, null, ['name_ar' => $product->name_ar]);

        return $product;
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        $this->activityLog->log('updated', 'products', $product, null, $data);

        return $product->fresh();
    }

    public function delete(Product $product): void
    {
        $this->activityLog->log('deleted', 'products', $product, ['name_ar' => $product->name_ar], null);

        $product->delete();
    }
}
