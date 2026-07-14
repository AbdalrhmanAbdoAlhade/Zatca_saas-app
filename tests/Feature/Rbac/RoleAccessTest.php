<?php

use App\Models\Company;
use App\Models\Plan;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    Plan::updateOrCreate(['slug' => 'free'], [
        'name' => 'Free', 'name_ar' => 'مجانية', 'max_users' => 5, 'max_invoices' => 100,
    ]);
});

function actingAsRole(string $slug): array
{
    $company = Company::factory()->create();
    $role = Role::where('slug', $slug)->firstOrFail();
    $user = User::factory()->for($company)->create(['role_id' => $role->id]);

    return [$company, $user];
}

it('viewer يقدر يشوف العملاء بس مينفعش ينشئ واحد', function () {
    [$company, $viewer] = actingAsRole('viewer');

    $this->actingAs($viewer, 'sanctum')->getJson('/api/v1/customers')->assertOk();

    $this->actingAs($viewer, 'sanctum')
        ->postJson('/api/v1/customers', ['name_ar' => 'محاولة إنشاء'])
        ->assertStatus(403);
});

it('sales يقدر ينشئ عميل بس مينفعش يحذفه', function () {
    [$company, $sales] = actingAsRole('sales');

    $createResponse = $this->actingAs($sales, 'sanctum')
        ->postJson('/api/v1/customers', ['name_ar' => 'عميل من سيلز']);
    $createResponse->assertStatus(201);

    $customerId = $createResponse->json('data.id');

    $this->actingAs($sales, 'sanctum')
        ->deleteJson("/api/v1/customers/{$customerId}")
        ->assertStatus(403);
});

it('accountant بس company-owner يقدروا يشوفوا activity-logs، مش sales أو viewer', function () {
    [$companyOwner, $owner] = actingAsRole('company-owner');
    [$companySales, $sales] = actingAsRole('sales');

    $this->actingAs($owner, 'sanctum')->getJson('/api/v1/activity-logs')->assertOk();
    $this->actingAs($sales, 'sanctum')->getJson('/api/v1/activity-logs')->assertStatus(403);
});

it('يوزر inactive مينفعش يستخدم أي endpoint حتى لو معاه توكن صالح', function () {
    [$company, $user] = actingAsRole('company-owner');
    $user->update(['status' => 'inactive']);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/customers')
        ->assertStatus(403);
});

it('يوزر تابع لشركة موقوفة مينفعش يستخدم أي endpoint', function () {
    [$company, $user] = actingAsRole('company-owner');
    $company->update(['status' => 'suspended']);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/customers')
        ->assertStatus(403);
});
