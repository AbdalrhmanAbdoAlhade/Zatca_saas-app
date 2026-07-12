<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();

            // ننسخ اسم المنتج وقت الفاتورة عشان الفاتورة متتأثرش لو المنتج اتعدل بعدين
            $table->string('name_ar');
            $table->string('name_en')->nullable();

            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(15.00);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0); // (qty*price - discount) + tax

            $table->timestamps();

            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
