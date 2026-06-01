<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropUnique('courses_class_code_unique');
            $table->unique(
                ['class_code', 'subject_code', 'subject_type'],
                'unique_course_code_subject_type'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropUnique('unique_course_code_subject_type');
            $table->unique('class_code');
        });
    }
};
