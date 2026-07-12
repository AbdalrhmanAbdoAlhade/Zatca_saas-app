<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();

            $table->date('start_date');
            $table->date('end_date');

            // حدود استخدام مأخوذة من الخطة وقت الاشتراك (تسمح بتخصيص لاحق لكل شركة لو احتجنا)
            $table->unsignedInteger('invoices_limit')->default(0);
            $table->unsignedInteger('users_limit')->default(0);
            $table->unsignedInteger('invoices_used')->default(0);
            $table->unsignedInteger('users_used')->default(0);

            $table->boolean('auto_renew')->default(true);
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');

            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
