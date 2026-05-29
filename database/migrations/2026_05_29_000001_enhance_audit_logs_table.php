<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('email')->nullable()->after('name');
            $table->string('role')->nullable()->after('email');
            $table->string('module')->nullable()->after('action')->index();
            $table->text('description')->nullable()->after('module');
            $table->string('target_type')->nullable()->after('description');
            $table->unsignedBigInteger('target_id')->nullable()->after('target_type');
            $table->json('before_values')->nullable()->after('target_id');
            $table->json('after_values')->nullable()->after('before_values');
            $table->string('severity')->default('info')->after('after_values')->index();

            $table->index(['action', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['action', 'created_at']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['target_type', 'target_id']);
            $table->dropIndex(['module']);
            $table->dropIndex(['severity']);
            $table->dropColumn([
                'user_id',
                'email',
                'role',
                'module',
                'description',
                'target_type',
                'target_id',
                'before_values',
                'after_values',
                'severity',
            ]);
        });
    }
};
