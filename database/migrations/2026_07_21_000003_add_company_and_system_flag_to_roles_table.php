<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * ⚠️ لو حصل خطأ "requires Doctrine DBAL": composer require doctrine/dbal
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // null = دور عام على مستوى النظام (زي company-owner) أو دور منصة عام.
            // له قيمة = دور مخصص أنشأته شركة معينة لموظفيها بس.
            $table->foreignId('company_id')->nullable()->after('id')
                ->constrained()->cascadeOnDelete();

            // الأدوار الستة الأساسية (company-owner, accountant, sales, viewer,
            // super-admin, platform-support) بتتعلم is_system=true تلقائياً
            // في PermissionSeeder - محمية من الحذف وتغيير الـ slug.
            $table->boolean('is_system')->default(false)->after('description');
        });

        // الأدوار الموجودة بالفعل (من roles migration الأصلية) هي كلها أدوار نظام
        DB::table('roles')->update(['is_system' => true]);
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'is_system']);
        });
    }
};
