<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'invoice_number' => 'INV-'.$this->faker->unique()->numerify('######'),
            'invoice_type' => 'tax_invoice',
            'invoice_status' => 'draft',
            'subtotal' => 100,
            'discount_amount' => 0,
            'tax_amount' => 15,
            'total_amount' => 115,
            'currency' => 'SAR',
            'issue_date' => now()->toDateString(),
            'uuid' => (string) Str::uuid(),
            'icv' => $this->faker->unique()->numberBetween(1, 999999),
            'zatca_status' => 'not_submitted',
        ];
    }
}
