<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up(): void
    {
        // إضافة fulltext index على الأعمدة
        Schema::table('properties', function (Blueprint $table) {
            $table->fullText(['title', 'description']);
        });
    }

    public function down(): void
    {
        // حذف الـ fulltext index عند rollback
        Schema::table('properties', function (Blueprint $table) {
            $table->dropFullText(['title', 'description']);
        });
    }
};
