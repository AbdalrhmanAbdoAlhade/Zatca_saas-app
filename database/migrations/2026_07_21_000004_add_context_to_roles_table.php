<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // company = دور مخصص لموظفين الشركات (نظام أو مخصص).
            // platform = دور مخصص لفريق المنصة نفسها (نظام أو مخصص).
            $table->enum('context', ['company', 'platform'])->default('company')->after('company_id');
        });

        DB::table('roles')
            ->whereIn('slug', ['super-admin', 'platform-support'])
            ->update(['context' => 'platform']);
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('context');
        });
    }
};
