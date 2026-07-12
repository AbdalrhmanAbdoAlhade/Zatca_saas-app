<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class SupplierService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        return Supplier::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name_ar', 'like', "%{$search}%")
                ->orWhere('name_en', 'like', "%{$search}%")
                ->orWhere('vat_number', 'like', "%{$search}%"))
            ->when(array_key_exists('is_active', $filters), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->latest()
            ->paginate($filters['per_page'] ?? 20);
    }

    public function create(array $data): Supplier
    {
        $data['company_id'] = Auth::user()->company_id;

        return Supplier::create($data);
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);

        return $supplier->fresh();
    }

    public function delete(Supplier $supplier): void
    {
        $supplier->delete();
    }
}
