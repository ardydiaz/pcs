<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropForeign(['faculty_id']);
        });

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE evaluations MODIFY faculty_id BIGINT UNSIGNED NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE evaluations ALTER COLUMN faculty_id DROP NOT NULL');
        }

        Schema::table('evaluations', function (Blueprint $table) {
            $table->foreign('faculty_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropForeign(['faculty_id']);
        });

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE evaluations MODIFY faculty_id BIGINT UNSIGNED NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE evaluations ALTER COLUMN faculty_id SET NOT NULL');
        }

        Schema::table('evaluations', function (Blueprint $table) {
            $table->foreign('faculty_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
