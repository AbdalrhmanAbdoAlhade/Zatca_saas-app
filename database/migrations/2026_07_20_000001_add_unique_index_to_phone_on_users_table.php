<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ⚠️ لو عندك بيانات حالية فيها رقمين هاتف متطابقين (أو الحقل فاضي عند
 * أكتر من يوزر)، الـ unique هتفشل على القيم المكررة الفعلية بس -
 * القيم NULL مسموح تتكرر عادي في MySQL/MariaDB مع unique index.
 * لو حصل تعارض، نضّف الأرقام المكررة يدوياً قبل ما تشغّل المايجريشن.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unique('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
        });
    }
};
