<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;

beforeEach(function () {
    // الأدوار متزروعة من جوه migration نفسها، هنتأكد بس إن plan موجودة للـ subscription
    Plan::updateOrCreate(['slug' => 'free'], [
        'name' => 'Free', 'name_ar' => 'مجانية', 'max_users' => 5, 'max_invoices' => 100,
    ]);
});

function actingAsCompanyOwner(?Company $company = null): array
{
    $company ??= Company::factory()->create();
    $ownerRole = Role::where('slug', 'company-owner')->firstOrFail();
    $user = User::factory()->for($company)->create(['role_id' => $ownerRole->id]);

    return [$company, $user];
}

it('لا يقدر يوزر شركة يشوف عميل تابع لشركة تانية', function () {
    [$companyA, $userA] = actingAsCompanyOwner();
    [$companyB, $userB] = actingAsCompanyOwner();

    $customerOfB = Customer::factory()->for($companyB)->create();

    $response = $this->actingAs($userA, 'sanctum')
        ->getJson("/api/v1/customers/{$customerOfB->id}");

    $response->assertStatus(404);
});

it('لا تظهر بيانات شركة تانية في index حتى لو نفس الجدول', function () {
    [$companyA, $userA] = actingAsCompanyOwner();
    [$companyB, $userB] = actingAsCompanyOwner();

    Customer::factory()->for($companyA)->count(3)->create();
    Customer::factory()->for($companyB)->count(5)->create();

    $response = $this->actingAs($userA, 'sanctum')->getJson('/api/v1/customers');

    $response->assertOk();
    expect($response->json('data.data'))->toHaveCount(3);
});

it('لا يقدر يوزر شركة يعدل فاتورة تابعة لشركة تانية حتى لو عرف الـ ID', function () {
    [$companyA, $userA] = actingAsCompanyOwner();
    [$companyB, $userB] = actingAsCompanyOwner();

    $invoiceOfB = Invoice::factory()->for($companyB)->create([
        'invoice_status' => 'draft',
    ]);

    $response = $this->actingAs($userA, 'sanctum')
        ->putJson("/api/v1/invoices/{$invoiceOfB->id}", ['notes' => 'حاول يعدل']);

    $response->assertStatus(404);
});

it('BelongsToCompany بيحط company_id تلقائي وقت الإنشاء من غير ما اليوزر يبعته', function () {
    [$company, $user] = actingAsCompanyOwner();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/customers', [
        'name_ar' => 'عميل تجريبي',
    ]);

    $response->assertStatus(201);
    expect(Customer::first()->company_id)->toBe($company->id);
});
