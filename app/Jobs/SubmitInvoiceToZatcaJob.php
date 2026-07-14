<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\Zatca\ZatcaSubmissionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SubmitInvoiceToZatcaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public array $backoff = [60, 300, 900, 1800, 3600]; // exponential-ish: دقيقة، 5، 15، 30، ساعة

    public function __construct(protected int $invoiceId)
    {
    }

    public function handle(ZatcaSubmissionService $submissionService): void
    {
        $invoice = Invoice::withoutGlobalScopes()->find($this->invoiceId);

        if (! $invoice) {
            return;
        }

        // Idempotency: لو اتقفلت خلاص (cleared/reported) متبعتهاش تاني حتى لو الـ job اتكرر
        if (in_array($invoice->zatca_status, ['cleared', 'reported'], true)) {
            return;
        }

        $submissionService->submit($invoice);
    }

    public function failed(Throwable $exception): void
    {
        $invoice = Invoice::withoutGlobalScopes()->find($this->invoiceId);

        $invoice?->update([
            'zatca_status' => 'rejected',
            'zatca_response' => ['error' => $exception->getMessage(), 'failed_after_retries' => true],
        ]);

        report($exception);
    }
}
