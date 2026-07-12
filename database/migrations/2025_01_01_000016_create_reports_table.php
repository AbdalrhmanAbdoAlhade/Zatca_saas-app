<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('report_type', ['daily', 'monthly', 'quarterly', 'yearly', 'custom']);
            // خانة عامة تسمح بإضافة أنواع تقارير من موديولات مستقبلية (Inventory/POS/Payroll)
            // بدون تعديل الاسكيمة الأساسية
            $table->string('source_module')->default('invoices');

            $table->date('start_date');
            $table->date('end_date');

            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('data')->nullable();      // نتيجة التقرير المحسوبة/المخزّنة (cache)
            $table->string('file_path')->nullable(); // لو اتصدّر PDF/Excel

            $table->timestamps();

            $table->index(['company_id', 'report_type']);
            $table->index(['company_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
