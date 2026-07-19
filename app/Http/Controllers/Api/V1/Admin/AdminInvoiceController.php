<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminInvoiceResource;
use App\Services\Admin\AdminInvoiceService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AdminInvoiceController extends Controller
{
    use ApiResponse;

    public function __construct(protected AdminInvoiceService $invoiceService)
    {
    }

    public function index(Request $request)
    {
        $invoices = $this->invoiceService->list($request->only([
            'company_id', 'invoice_type', 'invoice_status', 'zatca_status', 'from', 'to', 'per_page',
        ]));

        return $this->success(AdminInvoiceResource::collection($invoices)->response()->getData(true));
    }

    public function show(int $id)
    {
        return $this->success(new AdminInvoiceResource($this->invoiceService->show($id)));
    }
}
