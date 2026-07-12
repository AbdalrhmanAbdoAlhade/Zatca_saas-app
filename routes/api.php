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
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // مسارات عامة (بدون توكن)
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // مسارات محتاجة توكن (auth:sanctum)
    Route::middleware('auth:sanctum')->group(function () {

        // الحساب والجلسات
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAllDevices']);
        Route::get('sessions', [AuthController::class, 'sessions']);
        Route::delete('sessions/{tokenId}', [AuthController::class, 'revokeSession']);

        // الكيانات الأساسية
        Route::apiResource('customers', CustomerController::class);
        Route::apiResource('suppliers', SupplierController::class);
        Route::apiResource('products', ProductController::class);

        // الفواتير
        Route::apiResource('invoices', InvoiceController::class);

        // المدفوعات (على الفواتير) - بدون update/destroy حفاظاً على السجل المالي
        Route::apiResource('payments', PaymentController::class)->only(['index', 'store', 'show']);

        // مدفوعات الاشتراكات - عرض فقط (بتتسجل من webhook بوابة الدفع)
        Route::apiResource('subscription-payments', SubscriptionPaymentController::class)->only(['index', 'show']);

        // سجل النشاطات - عرض فقط
        Route::apiResource('activity-logs', ActivityLogController::class)->only(['index', 'show']);

        // التقارير
        Route::apiResource('reports', ReportController::class)->only(['index', 'store', 'show', 'destroy']);
    });
});
