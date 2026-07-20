<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('faculty_courses', 'department')) {
            Schema::table('faculty_courses', function (Blueprint $table) {
                $table->string('department')->nullable()->after('semester');
                $table->index('department', 'faculty_courses_department_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('faculty_courses', 'department')) {
            Schema::table('faculty_courses', function (Blueprint $table) {
                $table->dropIndex('faculty_courses_department_index');
                $table->dropColumn('department');
            });
        }
    }
};
