<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_zatca_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // بيئة التشغيل
            $table->enum('environment', ['sandbox', 'simulation', 'production'])->default('sandbox');

            // اعتماديات مرحلة الـ Onboarding
            $table->string('otp')->nullable();
            $table->text('client_id')->nullable();
            $table->text('client_secret')->nullable();

            // الأصول التشفيرية (مشفّرة عبر Laravel Crypt على مستوى الـ Model casts)
            $table->text('csr')->nullable();
            $table->text('private_key')->nullable();
            $table->text('certificate')->nullable();       // Compliance CSID certificate
            $table->text('production_certificate')->nullable();
            $table->text('secret_key')->nullable();
            $table->text('binary_security_token')->nullable();

            // بيانات الامتثال (Compliance)
            $table->string('request_id')->nullable();
            $table->string('compliance_request_id')->nullable();
            $table->enum('compliance_status', ['not_started', 'pending', 'passed', 'failed'])->default('not_started');

            // توكنات الجلسة
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('certificate_expiry_date')->nullable();

            // مرحلة الـ onboarding الحالية (1: CSR/Compliance CSID, 2: Compliance check, 3: Production CSID, 4: Production)
            $table->unsignedTinyInteger('onboarding_stage')->default(0);
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();

            $table->unique('company_id'); // كل شركة ليها إعداد ZATCA واحد بس
            $table->index('environment');
            $table->index('compliance_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_zatca_settings');
    }
};
