<?php

namespace App\Models; // Define the namespace

use Illuminate\Database\Eloquent\Model; // Make sure to import the base Model class

class AuditLogs extends Model
{
    protected $table = 'audit_logs'; // Specify the table name if it doesn't follow Laravel's naming convention

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'role',
        'action',
        'module',
        'description',
        'target_type',
        'target_id',
        'before_values',
        'after_values',
        'severity',
        'method',
        'ipAddress',
        'userAgent',
    ];

    protected $casts = [
        'before_values' => 'array',
        'after_values' => 'array',
    ];
}
 
