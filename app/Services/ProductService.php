<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ProductService
{
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

        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
