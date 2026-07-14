<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name_ar' => $this->faker->words(2, true),
            'name_en' => $this->faker->words(2, true),
            'sku' => $this->faker->unique()->bothify('SKU-####'),
            'unit_price' => $this->faker->randomFloat(2, 10, 500),
            'tax_percentage' => 15,
            'unit' => 'unit',
            'is_active' => true,
        ];
    }
}
