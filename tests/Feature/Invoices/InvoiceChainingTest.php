<?php

use App\Models\Company;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;

beforeEach(function () {
    // Plan مجاني
    Plan::updateOrCreate(['slug' => 'free'], [
        'name' => 'Free', 'name_ar' => 'مجانية', 'max_users' => 5, 'max_invoices' => 100,
    ]);

    // Roles
    Role::firstOrCreate(['slug' => 'company-owner'], ['name' => 'Company Owner', 'name_ar' => 'مالك الشركة']);
    Role::firstOrCreate(['slug' => 'sales'], ['name' => 'Sales', 'name_ar' => 'مبيعات']);
    Role::firstOrCreate(['slug' => 'viewer'], ['name' => 'Viewer', 'name_ar' => 'مشاهد']);
    Role::firstOrCreate(['slug' => 'accountant'], ['name' => 'Accountant', 'name_ar' => 'محاسب']);
});

// ✅ FIX: helper function عشان نعمل subscription للشركة
function createCompanyWithSubscription(): Company
{
    $company = Company::factory()->create();

    $freePlan = Plan::where('slug', 'free')->first();

    if ($freePlan) {
        Subscription::create([
            'company_id' => $company->id,
            'plan_id' => $freePlan->id,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'invoices_limit' => $freePlan->max_invoices,
            'users_limit' => $freePlan->max_users,
            'auto_renew' => true,
            'status' => 'active',
        ]);
    }

    return $company;
}

function createInvoicePayload(Product $product): array
{
    return [
        'invoice_type' => 'tax_invoice',
        'issue_date' => now()->toDateString(),
        'items' => [
            [
                'name_ar' => $product->name_ar,
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => 50,
                'tax_percentage' => 15,
            ],
        ],
    ];
}

it('أول فاتورة للشركة تاخد icv = 1 وprevious_invoice_hash = null', function () {
    $company = createCompanyWithSubscription(); // ✅ FIX
    $ownerRole = Role::where('slug', 'company-owner')->firstOrFail();
    $user = User::factory()->for($company)->create(['role_id' => $ownerRole->id]);
    $product = Product::factory()->for($company)->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/invoices', createInvoicePayload($product));

    $response->assertStatus(201);
    expect($response->json('data.icv'))->toBe(1);

    $invoice = \App\Models\Invoice::first();
    expect($invoice->previous_invoice_hash)->toBeNull();
    expect($invoice->invoice_hash)->not->toBeNull();
});

it('تاني فاتورة تاخد icv = 2 وprevious_invoice_hash = هاش الفاتورة الأولى', function () {
    $company = createCompanyWithSubscription(); // ✅ FIX
    $ownerRole = Role::where('slug', 'company-owner')->firstOrFail();
    $user = User::factory()->for($company)->create(['role_id' => $ownerRole->id]);
    $product = Product::factory()->for($company)->create();

    $this->actingAs($user, 'sanctum')->postJson('/api/v1/invoices', createInvoicePayload($product));
    $first = \App\Models\Invoice::first();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/invoices', createInvoicePayload($product));

    $response->assertStatus(201);
    expect($response->json('data.icv'))->toBe(2);

    $second = \App\Models\Invoice::orderByDesc('id')->first();
    expect($second->previous_invoice_hash)->toBe($first->invoice_hash);
});

it('تسلسل ICV منفصل تمامااً لكل شركة (شركة تانية تبدأ من 1 برضو)', function () {
    $companyA = createCompanyWithSubscription(); // ✅ FIX
    $companyB = createCompanyWithSubscription(); // ✅ FIX
    $ownerRole = Role::where('slug', 'company-owner')->firstOrFail();

    $userA = User::factory()->for($companyA)->create(['role_id' => $ownerRole->id]);
    $userB = User::factory()->for($companyB)->create(['role_id' => $ownerRole->id]);

    $productA = Product::factory()->for($companyA)->create();
    $productB = Product::factory()->for($companyB)->create();

    $this->actingAs($userA, 'sanctum')->postJson('/api/v1/invoices', createInvoicePayload($productA));
    $this->actingAs($userA, 'sanctum')->postJson('/api/v1/invoices', createInvoicePayload($productA));

    $responseB = $this->actingAs($userB, 'sanctum')
        ->postJson('/api/v1/invoices', createInvoicePayload($productB));

    expect($responseB->json('data.icv'))->toBe(1);
});

it('مينفعش تعدل أو تحذف فاتورة اتبعتت خلاص (مش draft)', function () {
    $company = createCompanyWithSubscription(); // ✅ FIX
    $ownerRole = Role::where('slug', 'company-owner')->firstOrFail();
    $user = User::factory()->for($company)->create(['role_id' => $ownerRole->id]);

    $invoice = \App\Models\Invoice::factory()->for($company)->create([
        'invoice_status' => 'submitted',
    ]);

    $updateResponse = $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/invoices/{$invoice->id}", ['notes' => 'محاولة تعديل']);
    $updateResponse->assertStatus(422);

    $deleteResponse = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/invoices/{$invoice->id}");
    $deleteResponse->assertStatus(422);
});
