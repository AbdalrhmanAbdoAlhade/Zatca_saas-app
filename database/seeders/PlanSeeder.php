<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'name_ar' => 'مجانية',
                'slug' => 'free',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'max_users' => 2,
                'max_invoices' => 30,
                'zatca_integration' => true,
                'reports_access' => false,
            ],
            [
                'name' => 'Basic',
                'name_ar' => 'أساسية',
                'slug' => 'basic',
                'price_monthly' => 99,
                'price_yearly' => 990,
                'max_users' => 5,
                'max_invoices' => 500,
                'zatca_integration' => true,
                'reports_access' => true,
            ],
            [
                'name' => 'Pro',
                'name_ar' => 'احترافية',
                'slug' => 'pro',
                'price_monthly' => 299,
                'price_yearly' => 2990,
                'max_users' => 20,
                'max_invoices' => 5000,
                'zatca_integration' => true,
                'reports_access' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
