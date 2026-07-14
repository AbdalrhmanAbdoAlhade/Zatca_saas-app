<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\Zatca\ZatcaInvoiceProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GenerateInvoiceXmlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(protected int $invoiceId)
    {
    }

    public function handle(ZatcaInvoiceProcessingService $processingService): void
    {
        $invoice = Invoice::withoutGlobalScopes()->find($this->invoiceId);

        if (! $invoice || $invoice->xml_path) {
            return; // اتعملت خلاص - idempotency
        }

        $processingService->generateXmlAndQr($invoice);
    }

    public function failed(Throwable $exception): void
    {
        report($exception);
    }
}
