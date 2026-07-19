<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    public function list(array $filters = []): LengthAwarePaginator
    {
        return Payment::query()
            ->when($filters['invoice_id'] ?? null, fn ($q, $invoiceId) => $q->where('invoice_id', $invoiceId))
            ->latest('paid_at')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function create(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $data['company_id'] = Auth::user()->company_id;
            $data['received_by'] = Auth::id();

            $payment = Payment::create($data);

            $invoice = Invoice::findOrFail($data['invoice_id']);
            $totalPaid = $invoice->payments()->sum('amount');

            if ($totalPaid >= $invoice->total_amount && $invoice->invoice_status !== 'paid') {
                $invoice->update(['invoice_status' => 'paid']);
            }

            $this->activityLog->log('created', 'payments', $payment, null, [
                'invoice_id' => $payment->invoice_id,
                'amount' => $payment->amount,
            ]);

            return $payment;
        });
    }
}
