<?php

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('تسجيل دخول ببيانات صحيحة بيرجع توكن', function () {
    $company = Company::factory()->create();
    $role = Role::where('slug', 'company-owner')->firstOrFail();
    $user = User::factory()->for($company)->create([
        'role_id' => $role->id,
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertOk();
    expect($response->json('data.token'))->not->toBeEmpty();
});

it('تسجيل دخول بباسورد غلط بيرجع 401', function () {
    $company = Company::factory()->create();
    $role = Role::where('slug', 'company-owner')->firstOrFail();
    User::factory()->for($company)->create([
        'role_id' => $role->id,
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401);
});

it('logout بيلغي التوكن الحالي بس مش باقي الأجهزة', function () {
    $company = Company::factory()->create();
    $role = Role::where('slug', 'company-owner')->firstOrFail();
    $user = User::factory()->for($company)->create(['role_id' => $role->id]);

    $tokenA = $user->createToken('device-a')->plainTextToken;
    $tokenB = $user->createToken('device-b')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$tokenA}")
        ->postJson('/api/v1/logout')
        ->assertOk();

    expect($user->tokens()->count())->toBe(1);
});
