<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * الكتالوج الكامل - group بيخدم تجميع الصلاحيات في واجهة إنشاء الأدوار.
     */
    protected function catalog(): array
    {
        return [
            'customers' => ['view', 'create', 'update', 'delete'],
            'suppliers' => ['view', 'create', 'update', 'delete'],
            'products' => ['view', 'create', 'update', 'delete'],
            'invoices' => ['view', 'create', 'update', 'delete', 'process_zatca'],
            'payments' => ['view', 'create'],
            'reports' => ['view', 'create', 'delete'],
            'company_users' => ['view', 'create', 'update', 'delete'],
            'company' => ['view', 'update'],
            'zatca' => ['view', 'manage'],
            'activity_logs' => ['view'],
            'subscription_payments' => ['view'],
            'roles' => ['view', 'create', 'update', 'delete'],
            'admin_companies' => ['view', 'create', 'update', 'delete', 'manage_subscriptions'],
            'admin_plans' => ['view', 'create', 'update', 'delete'],
            'admin_staff' => ['view', 'create', 'update', 'delete'],
            'admin_invoices' => ['view'],
            'admin_activity_logs' => ['view'],
        ];
    }

    protected function labels(): array
    {
        return [
            'customers' => ['العملاء', 'Customers'],
            'suppliers' => ['الموردين', 'Suppliers'],
            'products' => ['المنتجات', 'Products'],
            'invoices' => ['الفواتير', 'Invoices'],
            'payments' => ['المدفوعات', 'Payments'],
            'reports' => ['التقارير', 'Reports'],
            'company_users' => ['موظفي الشركة', 'Company Users'],
            'company' => ['بيانات الشركة', 'Company'],
            'zatca' => ['ZATCA', 'ZATCA'],
            'activity_logs' => ['سجل الأنشطة', 'Activity Logs'],
            'subscription_payments' => ['مدفوعات الاشتراك', 'Subscription Payments'],
            'roles' => ['الأدوار والصلاحيات', 'Roles & Permissions'],
            'admin_companies' => ['[منصة] الشركات', '[Platform] Companies'],
            'admin_plans' => ['[منصة] الخطط', '[Platform] Plans'],
            'admin_staff' => ['[منصة] الفريق', '[Platform] Staff'],
            'admin_invoices' => ['[منصة] الفواتير', '[Platform] Invoices'],
            'admin_activity_logs' => ['[منصة] سجل الأنشطة', '[Platform] Activity Logs'],
        ];
    }

    /** الصلاحيات اللي كل دور نظام أساسي بياخدها - نفس منطق middleware role: الحالي بالظبط */
    protected function rolePermissionMap(): array
    {
        $companyWrite = ['customers', 'suppliers', 'products', 'invoices'];
        $companyDelete = ['customers', 'suppliers', 'products', 'invoices'];

        return [
            'company-owner' => [
                ...$this->expand($companyWrite, ['view', 'create', 'update', 'delete']),
                'payments.view', 'payments.create',
                'reports.view', 'reports.create', 'reports.delete',
                'company_users.view', 'company_users.create', 'company_users.update', 'company_users.delete',
                'company.view', 'company.update',
                'zatca.view', 'zatca.manage',
                'activity_logs.view',
                'subscription_payments.view',
                'roles.view', 'roles.create', 'roles.update', 'roles.delete',
                'invoices.process_zatca',
            ],
            'accountant' => [
                ...$this->expand($companyWrite, ['view', 'create', 'update', 'delete']),
                'payments.view', 'payments.create',
                'reports.view', 'reports.create', 'reports.delete',
                'zatca.view', 'zatca.manage',
                'invoices.process_zatca',
            ],
            'sales' => [
                ...$this->expand($companyWrite, ['view', 'create', 'update']),
                'invoices.process_zatca',
            ],
            'viewer' => [
                ...$this->expand($companyWrite, ['view']),
                'reports.view',
            ],
            'super-admin' => [
                'admin_companies.view', 'admin_companies.create', 'admin_companies.update',
                'admin_companies.delete', 'admin_companies.manage_subscriptions',
                'admin_plans.view', 'admin_plans.create', 'admin_plans.update', 'admin_plans.delete',
                'admin_staff.view', 'admin_staff.create', 'admin_staff.update', 'admin_staff.delete',
                'admin_invoices.view', 'admin_activity_logs.view',
                'roles.view', 'roles.create', 'roles.update', 'roles.delete',
            ],
            'platform-support' => [
                'admin_companies.view', 'admin_plans.view', 'admin_invoices.view', 'admin_activity_logs.view',
            ],
        ];
    }

    protected function expand(array $groups, array $actions): array
    {
        $result = [];
        foreach ($groups as $group) {
            foreach ($actions as $action) {
                $result[] = "{$group}.{$action}";
            }
        }

        return $result;
    }

    public function run(): void
    {
        $labels = $this->labels();

        foreach ($this->catalog() as $group => $actions) {
            [$nameAr] = $labels[$group] ?? [$group, $group];

            foreach ($actions as $action) {
                Permission::updateOrCreate(
                    ['slug' => "{$group}.{$action}"],
                    [
                        'name' => ucfirst(str_replace('_', ' ', $group)).' - '.ucfirst($action),
                        'name_ar' => $nameAr,
                        'group' => $group,
                    ]
                );
            }
        }

        foreach ($this->rolePermissionMap() as $slug => $permissionSlugs) {
            $role = Role::where('slug', $slug)->whereNull('company_id')->first();

            if (! $role) {
                $this->command?->warn("رول '{$slug}' مش موجود - تخطينا ربط صلاحياته.");

                continue;
            }

            $ids = Permission::whereIn('slug', $permissionSlugs)->pluck('id');
            $role->permissions()->sync($ids);
        }

        $this->command?->info('تم ربط الصلاحيات بالأدوار الأساسية بنجاح.');
    }
}
