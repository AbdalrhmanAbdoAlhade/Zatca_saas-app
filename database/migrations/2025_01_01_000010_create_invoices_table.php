<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->string('invoice_number'); // رقم داخلي يخص الشركة
            $table->enum('invoice_type', [
                'tax_invoice',
                'simplified_tax_invoice',
                'credit_note',
                'debit_note',
                'purchase_invoice',
                'expense_invoice',
            ]);

            $table->enum('invoice_status', [
                'draft', 'pending', 'submitted', 'paid', 'returned', 'rejected',
            ])->default('draft');

            $table->enum('payment_method', [
                'cash', 'bank_transfer', 'mada', 'visa', 'mastercard', 'credit',
            ])->nullable();

            // الأطراف
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();

            // المرجع (لإشعارات الدائن/المدين المرتبطة بفاتورة أصلية)
            $table->foreignId('reference_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();

            // القيم المالية
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->string('currency', 3)->default('SAR');

            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->string('notes')->nullable();

            // مخرجات التوليد
            $table->string('xml_path')->nullable();
            $table->string('pdf_path')->nullable();

            // عناصر الامتثال الإلزامية لـ ZATCA
            $table->uuid('uuid')->unique();
            $table->text('qr_code')->nullable(); // Base64 TLV
            $table->string('invoice_hash', 64)->nullable();          // SHA-256
            $table->string('previous_invoice_hash', 64)->nullable(); // تسلسل الهاش
            $table->unsignedBigInteger('icv')->nullable();           // Invoice Counter Value لكل شركة

            // استجابة ZATCA
            $table->string('zatca_uuid')->nullable();
            $table->enum('zatca_status', ['not_submitted', 'cleared', 'reported', 'warning', 'rejected'])->default('not_submitted');
            $table->json('zatca_response')->nullable();
            $table->timestamp('zatca_submitted_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'invoice_number']);
            $table->unique(['company_id', 'icv']); // تسلسل صارم لكل شركة
            $table->index(['company_id', 'invoice_type']);
            $table->index(['company_id', 'invoice_status']);
            $table->index(['company_id', 'issue_date']);
            $table->index('zatca_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
