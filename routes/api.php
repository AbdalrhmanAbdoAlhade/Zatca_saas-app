<?php

use App\Http\Controllers\Api\V1\ActivityLogController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SubscriptionPaymentController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\ZatcaController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // مسارات عامة (بدون توكن)
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // مسارات محتاجة توكن + الشركة/اليوزر لازم يكونوا active
    Route::middleware(['auth:sanctum', 'company.active'])->group(function () {

        // الحساب والجلسات - متاحة لأي دور
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAllDevices']);
        Route::get('sessions', [AuthController::class, 'sessions']);
        Route::delete('sessions/{tokenId}', [AuthController::class, 'revokeSession']);

        // القراءة متاحة لكل الأدوار المسجلة (بما فيهم viewer) - الـ CompanyScope trait بيحمي البيانات أصلاً
        Route::get('customers', [CustomerController::class, 'index']);
        Route::get('customers/{customer}', [CustomerController::class, 'show']);
        Route::get('suppliers', [SupplierController::class, 'index']);
        Route::get('suppliers/{supplier}', [SupplierController::class, 'show']);
        Route::get('products', [ProductController::class, 'index']);
        Route::get('products/{product}', [ProductController::class, 'show']);
        Route::get('invoices', [InvoiceController::class, 'index']);
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show']);
        Route::get('payments', [PaymentController::class, 'index']);
        Route::get('payments/{payment}', [PaymentController::class, 'show']);
        Route::get('reports', [ReportController::class, 'index']);
        Route::get('reports/{report}', [ReportController::class, 'show']);

        // كتابة (إنشاء/تعديل) - Owner, Accountant, Sales بس (مش Viewer)
        Route::middleware('role:company-owner,accountant,sales')->group(function () {
            Route::post('customers', [CustomerController::class, 'store']);
            Route::put('customers/{customer}', [CustomerController::class, 'update']);
            Route::post('suppliers', [SupplierController::class, 'store']);
            Route::put('suppliers/{supplier}', [SupplierController::class, 'update']);
            Route::post('products', [ProductController::class, 'store']);
            Route::put('products/{product}', [ProductController::class, 'update']);

            Route::post('invoices', [InvoiceController::class, 'store'])
                ->middleware('subscription.limit:invoices');
            Route::put('invoices/{invoice}', [InvoiceController::class, 'update']);
        });

        // حذف - Owner و Accountant بس
        Route::middleware('role:company-owner,accountant')->group(function () {
            Route::delete('customers/{customer}', [CustomerController::class, 'destroy']);
            Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy']);
            Route::delete('products/{product}', [ProductController::class, 'destroy']);
            Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy']);

            Route::post('payments', [PaymentController::class, 'store']);
            Route::post('reports', [ReportController::class, 'store']);
            Route::delete('reports/{report}', [ReportController::class, 'destroy']);
        });

        // مدفوعات الاشتراكات وسجل النشاطات - حساسة، Owner بس
        Route::middleware('role:company-owner')->group(function () {
            Route::apiResource('subscription-payments', SubscriptionPaymentController::class)->only(['index', 'show']);
            Route::apiResource('activity-logs', ActivityLogController::class)->only(['index', 'show']);
        });

        // ZATCA Onboarding - Owner و Accountant بس (بيانات حساسة جداً)
        Route::middleware('role:company-owner,accountant')->prefix('zatca')->group(function () {
            Route::get('settings', [ZatcaController::class, 'show']);
            Route::post('onboarding/compliance-csid', [ZatcaController::class, 'generateComplianceCsid']);
            Route::post('onboarding/compliance-check', [ZatcaController::class, 'complianceCheck']);
            Route::post('onboarding/production-csid', [ZatcaController::class, 'requestProductionCsid']);
            Route::post('onboarding/activate-production', [ZatcaController::class, 'activateProduction']);
        });
    });
});
