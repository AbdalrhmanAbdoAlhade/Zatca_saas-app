<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Report;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ReportService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        return Report::query()
            ->when($filters['report_type'] ?? null, fn ($q, $type) => $q->where('report_type', $type))
            ->latest()
            ->paginate($filters['per_page'] ?? 20);
    }

    public function generate(array $data): Report
    {
        $companyId = Auth::user()->company_id;

        $invoicesQuery = Invoice::where('company_id', $companyId)
            ->whereBetween('issue_date', [$data['start_date'], $data['end_date']]);

        $totalSales = (clone $invoicesQuery)
            ->whereIn('invoice_type', ['tax_invoice', 'simplified_tax_invoice'])
            ->sum('total_amount');

        $totalTax = (clone $invoicesQuery)->sum('tax_amount');

        $countsByStatus = (clone $invoicesQuery)
            ->selectRaw('invoice_status, count(*) as total')
            ->groupBy('invoice_status')
            ->pluck('total', 'invoice_status');

        $countsByType = (clone $invoicesQuery)
            ->selectRaw('invoice_type, count(*) as total')
            ->groupBy('invoice_type')
            ->pluck('total', 'invoice_type');

        $zatcaStatusBreakdown = (clone $invoicesQuery)
            ->selectRaw('zatca_status, count(*) as total')
            ->groupBy('zatca_status')
            ->pluck('total', 'zatca_status');

        return Report::create([
            'company_id' => $companyId,
            'generated_by' => Auth::id(),
            'report_type' => $data['report_type'],
            'source_module' => 'invoices',
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => 'completed',
            'data' => [
                'total_sales' => (float) $totalSales,
                'total_tax_collected' => (float) $totalTax,
                'invoice_counts_by_status' => $countsByStatus,
                'invoice_counts_by_type' => $countsByType,
                'zatca_status_breakdown' => $zatcaStatusBreakdown,
            ],
        ]);
    }
}
