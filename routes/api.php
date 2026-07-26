<?php

use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\V1\Admin\AdminActivityLogController;
use App\Http\Controllers\Api\V1\Admin\AdminInvoiceController;
use App\Http\Controllers\Api\V1\Admin\AdminStaffController;
use App\Http\Controllers\Api\V1\Admin\CompanyManagementController;
use App\Http\Controllers\Api\V1\Admin\PlanController;
use App\Http\Controllers\Api\V1\ActivityLogController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\SubscriptionPaymentController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ZatcaController;
use Illuminate\Support\Facades\Route;

Route::get('health', [HealthController::class, 'check']);

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

        // بروفايل الشركة وإعداداتها - قراءة لكل الأدوار
        Route::get('company', [CompanyController::class, 'show']);
        Route::get('company/settings', [CompanyController::class, 'showSettings']);

        // إدارة الموظفين - قراءة لكل الأدوار (بيانات الفريق مش سرية جوه الشركة)
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{user}', [UserController::class, 'show']);

        // كتابة (إنشاء/تعديل) - Owner, Accountant, Sales بس (مش Viewer)، أو أي دور
        // مخصص عنده الصلاحية المحددة (مثلاً customers.create)
        Route::group([], function () {
            Route::post('customers', [CustomerController::class, 'store'])
                ->middleware('role:company-owner,accountant,sales,customers.create');
            Route::put('customers/{customer}', [CustomerController::class, 'update'])
                ->middleware('role:company-owner,accountant,sales,customers.update');
            Route::post('suppliers', [SupplierController::class, 'store'])
                ->middleware('role:company-owner,accountant,sales,suppliers.create');
            Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])
                ->middleware('role:company-owner,accountant,sales,suppliers.update');
            Route::post('products', [ProductController::class, 'store'])
                ->middleware('role:company-owner,accountant,sales,products.create');
            Route::put('products/{product}', [ProductController::class, 'update'])
                ->middleware('role:company-owner,accountant,sales,products.update');

            Route::post('invoices', [InvoiceController::class, 'store'])
                ->middleware(['role:company-owner,accountant,sales,invoices.create', 'subscription.limit:invoices']);
            Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])
                ->middleware('role:company-owner,accountant,sales,invoices.update');
            Route::post('invoices/{invoice}/generate-xml', [InvoiceController::class, 'generateXml'])
                ->middleware('role:company-owner,accountant,sales,invoices.process_zatca');
            Route::post('invoices/{invoice}/sign-xml', [InvoiceController::class, 'signXml'])
                ->middleware('role:company-owner,accountant,sales,invoices.process_zatca');
            Route::post('invoices/{invoice}/submit-to-zatca', [InvoiceController::class, 'submitToZatca'])
                ->middleware('role:company-owner,accountant,sales,invoices.process_zatca');
            Route::post('invoices/{invoice}/process', [InvoiceController::class, 'process'])
                ->middleware('role:company-owner,accountant,sales,invoices.process_zatca');
        });

        // حذف - Owner و Accountant بس، أو دور مخصص عنده صلاحية الحذف المحددة
        Route::group([], function () {
            Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])
                ->middleware('role:company-owner,accountant,customers.delete');
            Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])
                ->middleware('role:company-owner,accountant,suppliers.delete');
            Route::delete('products/{product}', [ProductController::class, 'destroy'])
                ->middleware('role:company-owner,accountant,products.delete');
            Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])
                ->middleware('role:company-owner,accountant,invoices.delete');

            Route::post('payments', [PaymentController::class, 'store'])
                ->middleware('role:company-owner,accountant,payments.create');
            Route::post('reports', [ReportController::class, 'store'])
                ->middleware('role:company-owner,accountant,reports.create');
            Route::delete('reports/{report}', [ReportController::class, 'destroy'])
                ->middleware('role:company-owner,accountant,reports.delete');
        });

        // مدفوعات الاشتراكات وسجل النشاطات - حساسة، Owner بس
        Route::middleware('role:company-owner')->group(function () {
            Route::apiResource('subscription-payments', SubscriptionPaymentController::class)->only(['index', 'show']);
            Route::apiResource('activity-logs', ActivityLogController::class)->only(['index', 'show']);
        });

        // ZATCA Onboarding - Owner و Accountant بس (بيانات حساسة جداً)
        Route::middleware('role:company-owner,accountant')->prefix('zatca')->group(function () {
            Route::get('/', [ZatcaController::class, 'show']);
            Route::post('compliance-csid', [ZatcaController::class, 'generateComplianceCsid']);
            Route::post('compliance-check', [ZatcaController::class, 'complianceCheck']);
            Route::post('production-csid', [ZatcaController::class, 'requestProductionCsid']);
            Route::post('activate-production', [ZatcaController::class, 'activateProduction']);
        });

        // بروفايل الشركة، الإعدادات، وإدارة الموظفين - Owner بس
        Route::middleware('role:company-owner')->group(function () {
            Route::put('company', [CompanyController::class, 'update']);
            Route::put('company/settings', [CompanyController::class, 'updateSettings']);

            Route::post('users', [UserController::class, 'store'])
                ->middleware('subscription.limit:users');
            Route::put('users/{user}', [UserController::class, 'update']);
            Route::delete('users/{user}', [UserController::class, 'destroy']);

            // إدارة الأدوار المخصصة لموظفي الشركة (زي ما وضّحنا: الأدوار الأساسية
            // ثابتة، بس Owner يقدر يعمل أدوار إضافية مخصصة بصلاحيات محددة)
            Route::get('roles', [RoleController::class, 'index']);
            Route::get('roles/permissions-catalog', [RoleController::class, 'permissionsCatalog']);
            Route::post('roles', [RoleController::class, 'store']);
            Route::put('roles/{role}', [RoleController::class, 'update']);
            Route::delete('roles/{role}', [RoleController::class, 'destroy']);
        });

        // لوحة السوبر أدمن - عرض متاح لـ super-admin وplatform-support
        Route::middleware('role:super-admin,platform-support')->prefix('admin')->group(function () {
            Route::get('stats', [CompanyManagementController::class, 'stats']);
            Route::get('companies', [CompanyManagementController::class, 'index']);
            Route::get('companies/{id}', [CompanyManagementController::class, 'show']);
            Route::get('invoices', [AdminInvoiceController::class, 'index']);
            Route::get('invoices/{id}', [AdminInvoiceController::class, 'show']);
            Route::get('activity-logs', [AdminActivityLogController::class, 'index']);
            Route::get('plans', [PlanController::class, 'index']);
        });

        // تعليق/تفعيل/إنشاء/حذف الشركات + الاشتراكات + الخطط + فريق المنصة - super-admin بس
        Route::middleware('role:super-admin')->prefix('admin')->group(function () {
            Route::post('companies', [CompanyManagementController::class, 'store']);
            Route::delete('companies/{company}', [CompanyManagementController::class, 'destroy']);
            Route::put('companies/{company}/suspend', [CompanyManagementController::class, 'suspend']);
            Route::put('companies/{company}/activate', [CompanyManagementController::class, 'activate']);
            Route::post('companies/{company}/subscriptions', [CompanyManagementController::class, 'activateSubscription']);

            Route::post('plans', [PlanController::class, 'store']);
            Route::put('plans/{plan}', [PlanController::class, 'update']);
            Route::delete('plans/{plan}', [PlanController::class, 'destroy']);

            Route::get('staff', [AdminStaffController::class, 'index']);
            Route::post('staff', [AdminStaffController::class, 'store']);
            Route::put('staff/{staff}', [AdminStaffController::class, 'update']);
            Route::delete('staff/{staff}', [AdminStaffController::class, 'destroy']);

            // إدارة أدوار فريق المنصة نفسها - بديل ديناميكي لقائمة super-admin/platform-support الثابتة
            Route::get('roles', [RoleController::class, 'index']);
            Route::get('roles/permissions-catalog', [RoleController::class, 'permissionsCatalog']);
            Route::post('roles', [RoleController::class, 'store']);
            Route::put('roles/{role}', [RoleController::class, 'update']);
            Route::delete('roles/{role}', [RoleController::class, 'destroy']);
        });
    });
});
