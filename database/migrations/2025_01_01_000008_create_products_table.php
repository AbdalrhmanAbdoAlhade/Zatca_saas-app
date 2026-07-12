<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('sku')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();

            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(15.00); // القيمة المضافة الافتراضية بالسعودية
            $table->string('unit', 30)->default('unit'); // قطعة، كرتونة، كجم...

            // أعمدة جاهزة لموديول المخزون مستقبلاً (بدون كسر البنية الحالية)
            $table->boolean('track_stock')->default(false);
            $table->decimal('stock_quantity', 12, 2)->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
