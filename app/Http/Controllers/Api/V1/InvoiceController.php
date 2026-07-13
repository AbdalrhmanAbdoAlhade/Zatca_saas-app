<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\Zatca\ZatcaInvoiceProcessingService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use RuntimeException;

class InvoiceController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected InvoiceService $invoiceService,
        protected ZatcaInvoiceProcessingService $zatcaProcessingService,
    ) {
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
     * توليد XML (UBL 2.1 غير موقّع) + QR أساسي للفاتورة.
     * ملحوظة: ده مش الشكل النهائي المعتمد من ZATCA - راجع تحذيرات ZatcaInvoiceXmlBuilder.
     */
    public function generateXml(Invoice $invoice)
    {
        $invoice = $this->zatcaProcessingService->generateXmlAndQr($invoice);

        return $this->success(new InvoiceResource($invoice), __('invoices.xml_generated'));
    }
}
