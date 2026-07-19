<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class InvoiceService
{
    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    public function list(array $filters = []): LengthAwarePaginator
    {
        return Invoice::query()
            ->with(['customer', 'supplier'])
            ->when($filters['invoice_type'] ?? null, fn ($q, $type) => $q->where('invoice_type', $type))
            ->when($filters['invoice_status'] ?? null, fn ($q, $status) => $q->where('invoice_status', $status))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('issue_date', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('issue_date', '<=', $to))
            ->latest('issue_date')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function find(int $id): Invoice
    {
        return Invoice::with(['customer', 'supplier', 'items'])->findOrFail($id);
    }

    /**
     * إنشاء فاتورة جديدة + بنودها + ربط الـ ICV والـ Hash Chaining بشكل atomic
     * (lockForUpdate لمنع أي race condition لو اتعملت فاتورتين لنفس الشركة في نفس اللحظة).
     */
    public function create(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $companyId = Auth::user()->company_id;

            // نقفل آخر فاتورة للشركة عشان نضمن تسلسل الـ ICV والـ hash chaining
            $lastInvoice = Invoice::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->lockForUpdate()
                ->orderByDesc('icv')
                ->first();

            $nextIcv = ($lastInvoice->icv ?? 0) + 1;
            $previousHash = $lastInvoice->invoice_hash ?? null;

            [$items, $subtotal, $discountTotal, $taxTotal] = $this->calculateItems($data['items']);
            $totalAmount = $subtotal - $discountTotal + $taxTotal;

            $invoice = Invoice::create([
                'company_id' => $companyId,
                'invoice_number' => $this->generateInvoiceNumber($companyId, $nextIcv),
                'invoice_type' => $data['invoice_type'],
                'invoice_status' => 'draft',
                'payment_method' => $data['payment_method'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'reference_invoice_id' => $data['reference_invoice_id'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $discountTotal,
                'tax_amount' => $taxTotal,
                'total_amount' => $totalAmount,
                'currency' => $data['currency'] ?? 'SAR',
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'uuid' => (string) Str::uuid(),
                'previous_invoice_hash' => $previousHash,
                'icv' => $nextIcv,
                'zatca_status' => 'not_submitted',
                'created_by' => Auth::id(),
            ]);

            foreach ($items as $item) {
                $invoice->items()->create($item);
            }

            // نزود عداد الاستخدام في الاشتراك النشط (لو موجود) - يخدم SubscriptionLimit middleware
            $company = Company::find($companyId);
            $company?->activeSubscription?->increment('invoices_used');

            // Hash مبدئي مبني على بيانات الفاتورة الأساسية + الهاش السابق (chaining).
            // ملحوظة: هيتم استبداله بـ SHA-256 الحقيقي لملف الـ UBL XML لما ZatcaService يتبني.
            $invoice->update([
                'invoice_hash' => $this->computeChainHash($invoice, $previousHash),
            ]);

            $this->activityLog->log('created', 'invoices', $invoice, null, [
                'invoice_number' => $invoice->invoice_number,
                'total_amount' => $invoice->total_amount,
            ]);

            return $invoice->fresh(['items', 'customer', 'supplier']);
        });
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        if ($invoice->invoice_status !== 'draft') {
            throw new RuntimeException('only_draft_invoices_can_be_updated');
        }

        return DB::transaction(function () use ($invoice, $data) {
            if (isset($data['items'])) {
                [$items, $subtotal, $discountTotal, $taxTotal] = $this->calculateItems($data['items']);
                $data['subtotal'] = $subtotal;
                $data['discount_amount'] = $discountTotal;
                $data['tax_amount'] = $taxTotal;
                $data['total_amount'] = $subtotal - $discountTotal + $taxTotal;

                $invoice->items()->delete();
                foreach ($items as $item) {
                    $invoice->items()->create($item);
                }

                unset($data['items']);
            }

            $invoice->update($data);

            $this->activityLog->log('updated', 'invoices', $invoice, null, $data);

            return $invoice->fresh(['items', 'customer', 'supplier']);
        });
    }

    public function delete(Invoice $invoice): void
    {
        if ($invoice->invoice_status !== 'draft') {
            throw new RuntimeException('only_draft_invoices_can_be_deleted');
        }

        $this->activityLog->log('deleted', 'invoices', $invoice, ['invoice_number' => $invoice->invoice_number], null);

        $invoice->delete();
    }

    /**
     * @return array{0: array, 1: float, 2: float, 3: float} [items, subtotal, discountTotal, taxTotal]
     */
    protected function calculateItems(array $items): array
    {
        $subtotal = 0;
        $discountTotal = 0;
        $taxTotal = 0;
        $computed = [];

        foreach ($items as $item) {
            $lineSubtotal = $item['quantity'] * $item['unit_price'];
            $discount = $item['discount_amount'] ?? 0;
            $taxPercentage = $item['tax_percentage'] ?? 15;
            $taxableAmount = $lineSubtotal - $discount;
            $taxAmount = round($taxableAmount * ($taxPercentage / 100), 2);
            $lineTotal = $taxableAmount + $taxAmount;

            $computed[] = [
                'product_id' => $item['product_id'] ?? null,
                'name_ar' => $item['name_ar'],
                'name_en' => $item['name_en'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_amount' => $discount,
                'tax_percentage' => $taxPercentage,
                'tax_amount' => $taxAmount,
                'total_amount' => $lineTotal,
            ];

            $subtotal += $lineSubtotal;
            $discountTotal += $discount;
            $taxTotal += $taxAmount;
        }

        return [$computed, round($subtotal, 2), round($discountTotal, 2), round($taxTotal, 2)];
    }

    protected function generateInvoiceNumber(int $companyId, int $icv): string
    {
        return sprintf('INV-%d-%s', $companyId, str_pad((string) $icv, 6, '0', STR_PAD_LEFT));
    }

    protected function computeChainHash(Invoice $invoice, ?string $previousHash): string
    {
        $payload = implode('|', [
            $invoice->uuid,
            $invoice->company_id,
            $invoice->icv,
            $invoice->total_amount,
            $invoice->issue_date,
            $previousHash ?? '',
        ]);

        return hash('sha256', $payload);
    }
}
