<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->string('invoice_logo_path')->nullable();
            $table->string('primary_color', 7)->default('#000000');
            $table->string('secondary_color', 7)->default('#FFFFFF');

            $table->boolean('show_qr')->default(true);
            $table->boolean('show_vat')->default(true);
            $table->boolean('show_cr')->default(true);

            $table->decimal('default_tax_percentage', 5, 2)->default(15.00);
            $table->string('invoice_pdf_language', 2)->default('ar'); // ar / en (أو حسب باراميتر وقت التوليد)

            $table->timestamps();

            $table->unique('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
