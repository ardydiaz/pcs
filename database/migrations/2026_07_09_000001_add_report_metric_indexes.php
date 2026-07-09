<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->index(['is_active', 'academic_year', 'semester', 'faculty_id'], 'eval_report_filter_idx');
        });

        Schema::table('evaluation_responses', function (Blueprint $table) {
            $table->index(['evaluation_id', 'created_at'], 'eval_resp_eval_created_idx');
            $table->index(['schedule_id', 'effectiveness_rating'], 'eval_resp_schedule_rating_idx');
        });

        Schema::table('faculty_courses', function (Blueprint $table) {
            $table->index(['course_id', 'academic_year', 'semester'], 'faculty_courses_course_term_idx');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->index('subject_type', 'courses_subject_type_idx');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex('courses_subject_type_idx');
        });

        Schema::table('faculty_courses', function (Blueprint $table) {
            $table->dropIndex('faculty_courses_course_term_idx');
        });

        Schema::table('evaluation_responses', function (Blueprint $table) {
            $table->dropIndex('eval_resp_schedule_rating_idx');
            $table->dropIndex('eval_resp_eval_created_idx');
        });

        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropIndex('eval_report_filter_idx');
        });
    }
};
