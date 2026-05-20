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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_course_id')
                ->nullable()
                ->constrained('faculty_courses')
                ->nullOnDelete(); // allows null if related faculty_course is deleted
            $table->string('time')->nullable(); // e.g. "07:00a - 08:30a"
            $table->string('day')->nullable(); // e.g. "TTH" (multiple days)
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])
                ->nullable()
                ->default(null);
            $table->timestamps();

            // Indexes (these still work fine with nullable columns)
            $table->index(['faculty_course_id', 'day', 'time']);
            $table->index(['day', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
