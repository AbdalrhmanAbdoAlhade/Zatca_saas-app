<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // الهوية القانونية/التجارية
            $table->string('trade_name_ar');
            $table->string('trade_name_en')->nullable();
            $table->string('owner_name');
            $table->string('vat_number')->unique();
            $table->string('commercial_registration_number')->nullable();
            $table->string('tax_certificate_number')->nullable();

            // العنوان (مطلوب لفاتورة ZATCA)
            $table->string('country')->default('SA');
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('street')->nullable();
            $table->string('building_number', 10)->nullable();
            $table->string('additional_number', 10)->nullable();
            $table->string('postal_code', 10)->nullable();

            // ملفات التحقق
            $table->string('logo_path')->nullable();
            $table->string('identity_image_path')->nullable();
            $table->string('tax_certificate_file_path')->nullable();
            $table->string('commercial_registration_file_path')->nullable();

            // الحالة العامة للتينانت
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->string('default_locale', 2)->default('ar'); // ar / en

            // مالك الحساب (اختياري لتسهيل الاستعلام، الربط الحقيقي عبر users.company_id)
            $table->foreignId('owner_user_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
