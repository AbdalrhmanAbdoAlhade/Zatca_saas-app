<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'role_id' => Role::where('slug', 'company-owner')->value('id'),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('05########'),
            'password' => Hash::make('password'),
            'status' => 'active',
            'preferred_locale' => 'ar',
            'remember_token' => Str::random(10),
        ];
    }

    public function role(string $slug): static
    {
        return $this->state(fn () => [
            'role_id' => Role::where('slug', $slug)->value('id'),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
