<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'trade_name_ar' => $this->faker->company(),
            'trade_name_en' => $this->faker->company(),
            'owner_name' => $this->faker->name(),
            'vat_number' => (string) $this->faker->unique()->numerify('3#############3'),
            'commercial_registration_number' => $this->faker->numerify('##########'),
            'country' => 'SA',
            'city' => 'Riyadh',
            'status' => 'active',
            'default_locale' => 'ar',
        ];
    }
}
