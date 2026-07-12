<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class CustomerService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        return Customer::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name_ar', 'like', "%{$search}%")
                ->orWhere('name_en', 'like', "%{$search}%")
                ->orWhere('vat_number', 'like', "%{$search}%"))
            ->when(array_key_exists('is_active', $filters), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->latest()
            ->paginate($filters['per_page'] ?? 20);
    }

    public function create(array $data): Customer
    {
        $data['company_id'] = Auth::user()->company_id;

        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer->fresh();
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }
}
