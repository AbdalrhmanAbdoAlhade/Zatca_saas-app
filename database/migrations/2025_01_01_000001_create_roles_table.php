<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // super_admin, company_owner, accountant, sales, viewer
            $table->string('name_ar')->nullable();
            $table->string('slug')->unique(); // super-admin, company-owner, accountant, sales, viewer
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed default roles directly here to keep things simple for now
        DB::table('roles')->insert([
            ['name' => 'Super Admin', 'name_ar' => 'مدير النظام', 'slug' => 'super-admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Company Owner', 'name_ar' => 'مالك الشركة', 'slug' => 'company-owner', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Accountant', 'name_ar' => 'محاسب', 'slug' => 'accountant', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sales', 'name_ar' => 'مبيعات', 'slug' => 'sales', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Viewer', 'name_ar' => 'مشاهد', 'slug' => 'viewer', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
