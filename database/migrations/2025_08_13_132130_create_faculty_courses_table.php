<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('faculty_courses', function (Blueprint $table) {
            $table->id();
            // This will reference the 'faculties' table by default
            $table->foreignId('faculty_id')->constrained('faculties')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('section');
            $table->string('academic_year');
            $table->enum('semester', ['1st', '2nd', 'Summer']);
            $table->timestamps();

            // Ensure unique combination
            $table->unique(['faculty_id', 'course_id', 'section', 'academic_year', 'semester'], 'unique_faculty_course_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty_courses');
    }
};
