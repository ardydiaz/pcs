<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_responses', function (Blueprint $table) {
            $table->dropUnique('eval_resp_unique_student_schedule');
            $table->date('submitted_date')->nullable()->after('student_user_id');
        });

        DB::table('evaluation_responses')
            ->whereNull('submitted_date')
            ->update(['submitted_date' => DB::raw('DATE(created_at)')]);

        Schema::table('evaluation_responses', function (Blueprint $table) {
            $table->unique(
                ['evaluation_id', 'schedule_id', 'student_user_id', 'submitted_date'],
                'eval_resp_unique_student_schedule_daily'
            );
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_responses', function (Blueprint $table) {
            $table->dropUnique('eval_resp_unique_student_schedule_daily');
            $table->dropColumn('submitted_date');
            $table->unique(
                ['evaluation_id', 'schedule_id', 'student_user_id'],
                'eval_resp_unique_student_schedule'
            );
        });
    }
};
