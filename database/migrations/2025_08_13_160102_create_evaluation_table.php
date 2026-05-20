<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Create simplified evaluations table
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('users')->onDelete('cascade');
            $table->string('form_link')->unique(); // Unique shareable link
            $table->string('academic_year');
            $table->string('semester');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create evaluation responses table
        Schema::create('evaluation_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained()->onDelete('cascade');
            $table->foreignId('schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('course_code_snapshot')->nullable();
            $table->string('course_name_snapshot')->nullable();
            $table->string('schedule_time_snapshot')->nullable();
            $table->string('schedule_days_snapshot')->nullable();

            // Add IP address to track cooldown per IP per schedule
            $table->string('ip_address', 45)->nullable();

            // Fixed evaluation items
            $table->enum('effectiveness_rating', ['1', '2', '3', '4'])->nullable(); // 1=Not Effective, 4=Very Effective
            $table->text('feedback_comments')->nullable();

            $table->timestamps();

            // Index for cooldown tracking
            $table->index(['schedule_id', 'ip_address', 'created_at'], 'cooldown_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_responses');
        Schema::dropIfExists('evaluations');
    }
};
