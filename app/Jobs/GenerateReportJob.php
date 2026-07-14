<?php

namespace App\Jobs;

use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(protected int $reportId)
    {
    }

    public function handle(ReportService $reportService): void
    {
        $report = Report::withoutGlobalScopes()->find($this->reportId);

        if (! $report || $report->status === 'completed') {
            return;
        }

        $reportService->compute($report);
    }

    public function failed(Throwable $exception): void
    {
        Report::withoutGlobalScopes()->where('id', $this->reportId)->update(['status' => 'failed']);

        report($exception);
    }
}
