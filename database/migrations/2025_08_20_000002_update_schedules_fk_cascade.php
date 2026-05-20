<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['faculty_course_id']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->foreign('faculty_course_id')
                ->references('id')
                ->on('faculty_courses')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['faculty_course_id']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->foreign('faculty_course_id')
                ->references('id')
                ->on('faculty_courses')
                ->nullOnDelete();
        });
    }
};
