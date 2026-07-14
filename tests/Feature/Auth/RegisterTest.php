<?php

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

beforeEach(function () {
    Plan::updateOrCreate(['slug' => 'free'], [
        'name' => 'Free', 'name_ar' => 'مجانية', 'max_users' => 5, 'max_invoices' => 100,
    ]);
});

it('تسجيل شركة جديدة بينشئ الشركة والمالك والإعدادات الافتراضية والاشتراك المجاني مع بعض', function () {
    $response = $this->postJson('/api/v1/register', [
        'company' => [
            'trade_name_ar' => 'شركة تجريبية',
            'vat_number' => '300000000000003',
        ],
        'owner' => [
            'name' => 'Ahmed Owner',
            'email' => 'owner@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ],
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('success', true);
    expect($response->json('data.token'))->not->toBeEmpty();

    $company = Company::first();
    expect($company)->not->toBeNull();
    expect($company->owner_user_id)->not->toBeNull();

    expect($company->zatcaSettings)->not->toBeNull();
    expect($company->settings)->not->toBeNull();

    $subscription = Subscription::where('company_id', $company->id)->first();
    expect($subscription)->not->toBeNull();
    expect($subscription->status)->toBe('active');
});

it('مينفعش تسجل بنفس الرقم الضريبي مرتين', function () {
    $this->postJson('/api/v1/register', [
        'company' => [
            'trade_name_ar' => 'شركة 1',
            'vat_number' => '300000000000003',
            'owner_name' => 'Owner 1' // إضافتها هنا لتفادي الخطأ
        ],
        'owner' => [
            'name' => 'Owner 1',
            'email' => 'owner1@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ],
    ])->assertStatus(201);

    $this->postJson('/api/v1/register', [
        'company' => [
            'trade_name_ar' => 'شركة 2',
            'vat_number' => '300000000000003', // نفس الرقم الضريبي للتحقق من الفشل
            'owner_name' => 'Owner 2'
        ],
        'owner' => [
            'name' => 'Owner 2',
            'email' => 'owner2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ],
    ])->assertStatus(422); // التوقع هنا يجب أن يكون 422 Unprocessable Entity لفشل التحقق من التفرد
});
