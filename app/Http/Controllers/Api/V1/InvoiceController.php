<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Jobs\GenerateInvoiceXmlJob;
use App\Jobs\SignInvoiceXmlJob;
use App\Jobs\SubmitInvoiceToZatcaJob;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use RuntimeException;

class InvoiceController extends Controller
{
    use ApiResponse;

    public function __construct(protected InvoiceService $invoiceService)
    {
    }

    public function index(Request $request)
    {
        $invoices = $this->invoiceService->list(
            $request->only(['invoice_type', 'invoice_status', 'from', 'to', 'per_page'])
        );

        return $this->success(InvoiceResource::collection($invoices)->response()->getData(true));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $invoice = $this->invoiceService->create($request->validated());

        return $this->success(new InvoiceResource($invoice), __('messages.created_successfully'), 201);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['items', 'customer', 'supplier']);

        return $this->success(new InvoiceResource($invoice));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        try {
            $invoice = $this->invoiceService->update($invoice, $request->validated());
        } catch (RuntimeException $e) {
            return $this->error(__('invoices.'.$e->getMessage()), 422);
        }

        return $this->success(new InvoiceResource($invoice), __('messages.updated_successfully'));
    }

    public function destroy(Invoice $invoice)
    {
        try {
            $this->invoiceService->delete($invoice);
        } catch (RuntimeException $e) {
            return $this->error(__('invoices.'.$e->getMessage()), 422);
        }

        return $this->success(null, __('messages.deleted_successfully'));
    }

    /**
     * توليد XML (UBL 2.1 غير موقّع) + QR أساسي للفاتورة - async.
     */
    public function generateXml(Invoice $invoice)
    {
        GenerateInvoiceXmlJob::dispatch($invoice->id);

        return $this->success(new InvoiceResource($invoice), __('invoices.xml_queued'), 202);
    }

    /**
     * التوقيع الرقمي الفعلي (XAdES) - async، لازم generateXml يتعمل الأول.
     */
    public function signXml(Invoice $invoice)
    {
        if (! $invoice->xml_path) {
            return $this->error(__('invoices.generate_xml_first'), 422);
        }

        SignInvoiceXmlJob::dispatch($invoice->id);

        return $this->success(new InvoiceResource($invoice), __('invoices.sign_queued'), 202);
    }

    /**
     * إرسال الفاتورة الموقّعة لـ ZATCA فعلياً - async مع retry تلقائي.
     */
    public function submitToZatca(Invoice $invoice)
    {
        SubmitInvoiceToZatcaJob::dispatch($invoice->id);

        return $this->success(new InvoiceResource($invoice), __('invoices.submission_queued'), 202);
    }

    /**
     * يعمل الرحلة كاملة (Generate → Sign → Submit) كـ job chain واحدة -
     * أسهل للفرونت من نداء الـ 3 endpoints واحد ورا التاني.
     */
    public function process(Invoice $invoice)
    {
        Bus::chain([
            new GenerateInvoiceXmlJob($invoice->id),
            new SignInvoiceXmlJob($invoice->id),
            new SubmitInvoiceToZatcaJob($invoice->id),
        ])->dispatch();

        return $this->success(new InvoiceResource($invoice), __('invoices.processing_queued'), 202);
    }
}
