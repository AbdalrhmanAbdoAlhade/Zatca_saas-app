<?php

namespace App\Services;

use App\Jobs\GenerateReportJob;
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

    /**
     * بينشئ سجل التقرير فوراً بحالة processing، ويبعت Job يحسب البيانات في الخلفية.
     */
    public function requestGeneration(array $data): Report
    {
        $report = Report::create([
            'company_id' => Auth::user()->company_id,
            'generated_by' => Auth::id(),
            'report_type' => $data['report_type'],
            'source_module' => 'invoices',
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => 'pending',
        ]);

        GenerateReportJob::dispatch($report->id);

        return $report;
    }

    /**
     * بتتنادى من جوه GenerateReportJob بس - الحساب الفعلي.
     */
    public function compute(Report $report): void
    {
        $report->update(['status' => 'processing']);

        $invoicesQuery = Invoice::withoutGlobalScopes()
            ->where('company_id', $report->company_id)
            ->whereBetween('issue_date', [$report->start_date, $report->end_date]);

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

        $report->update([
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
