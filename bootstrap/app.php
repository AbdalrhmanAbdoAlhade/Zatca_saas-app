<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureCompanyIsActive;
use App\Http\Middleware\RoleCheck;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SubscriptionLimit; // تم حذف التكرار السطر التالي له هنا

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
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
    }) // تم إصلاح القوس هنا لإكمال السلسلة بشكل صحيح
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
