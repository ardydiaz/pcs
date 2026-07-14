<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_responses', function (Blueprint $table) {
            $table->foreignId('student_user_id')
                ->nullable()
                ->after('schedule_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->unique(
                ['evaluation_id', 'schedule_id', 'student_user_id'],
                'eval_resp_unique_student_schedule'
            );
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_responses', function (Blueprint $table) {
            $table->dropUnique('eval_resp_unique_student_schedule');
            $table->dropForeign(['student_user_id']);
            $table->dropColumn('student_user_id');
        });
    }
};
