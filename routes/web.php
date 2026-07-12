<?php

/*
|--------------------------------------------------------------------------
| تعليمات: افتح bootstrap/app.php وحدّث دالة withMiddleware كالتالي
|--------------------------------------------------------------------------
|
| لو الدالة مش موجودة أصلاً حطها، ولو موجودة زوّد عليها الأسطر الجديدة بس.
|
*/

use App\Http\Middleware\EnsureCompanyIsActive;
use App\Http\Middleware\RoleCheck;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SubscriptionLimit;
use Illuminate\Foundation\Configuration\Middleware;

// جوه ->withMiddleware(function (Middleware $middleware) { ... }) في bootstrap/app.php:

/*
->withMiddleware(function (Middleware $middleware) {

    // بيتطبق على كل مسارات الـ API تلقائياً (تحديد اللغة من الهيدر)
    $middleware->api(append: [
        SetLocale::class,
    ]);

    // aliases عشان نقدر نستخدمها بالاسم في الراوتس زي: ->middleware('company.active')
    $middleware->alias([
        'company.active' => EnsureCompanyIsActive::class,
        'role' => RoleCheck::class,
        'subscription.limit' => SubscriptionLimit::class,
    ]);

})
*/
