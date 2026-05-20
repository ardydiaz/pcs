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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id(); // Primary key for the audit log entry

            // Audit Logs Information
            $table->string('name')->nullable()->comment('Name of the user who performed the action'); // e.g., "John Doe"
            $table->string('action')->nullable()->comment('Description of the action performed e.g user registration, login, log out'); // e.g., user actions like "Created a new faculty record", "Logged in", "Logged out", "Updated course schedule"
            $table->string('method')->nullable()->comment('HTTP method used for the action e.g GET, POST, PUT, DELETE'); // e.g., "POST"
            $table->string('ipAddress')->nullable()->comment('IP address from which the action was performed'); // e.g., "192.168.1.1"
            $table->string('userAgent')->nullable()->comment('User agent string of the browser or client used for the action'); // e.g., "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3"   
        
            // Audit Information
            $table->timestamps(); // created_at will serve as the timestamp for when the action occurred
            $table->softDeletes(); // deleted_at will indicate if the log entry was soft-deleted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
