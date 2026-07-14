<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name_ar' => $this->faker->company(),
            'name_en' => $this->faker->company(),
            'vat_number' => (string) $this->faker->numerify('3#############3'),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->numerify('05########'),
            'city' => 'Riyadh',
            'country' => 'SA',
            'is_active' => true,
        ];
    }
}
