<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ⚠️ لو حصل خطأ "requires Doctrine DBAL" وقت التشغيل: composer require doctrine/dbal
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->change();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });
    }
};
