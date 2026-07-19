<?php

namespace App\Services\Admin;

use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminInvoiceService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        return Invoice::withoutGlobalScopes()
            ->with(['company:id,trade_name_ar,trade_name_en', 'customer'])
            ->when($filters['company_id'] ?? null, fn ($q, $companyId) => $q->where('company_id', $companyId))
            ->when($filters['invoice_type'] ?? null, fn ($q, $type) => $q->where('invoice_type', $type))
            ->when($filters['invoice_status'] ?? null, fn ($q, $status) => $q->where('invoice_status', $status))
            ->when($filters['zatca_status'] ?? null, fn ($q, $status) => $q->where('zatca_status', $status))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('issue_date', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('issue_date', '<=', $to))
            ->latest('issue_date')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function show(int $id): Invoice
    {
        return Invoice::withoutGlobalScopes()
            ->with(['company', 'customer', 'supplier', 'items'])
            ->findOrFail($id);
    }
}
