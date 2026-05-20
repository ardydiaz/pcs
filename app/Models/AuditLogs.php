<?php

namespace App\Models; // Define the namespace

use Illuminate\Database\Eloquent\Model; // Make sure to import the base Model class

class AuditLogs extends Model
{
    protected $table = 'audit_logs'; // Specify the table name if it doesn't follow Laravel's naming convention

    protected $fillable = [ // mass attributable fields
        'name', // Name of the user who performed the action e.g John Doe
        'action', // Description of the action performed (e.g., faculty_created, login, logout)
        'method', // HTTP method used for the action (e.g., GET, POST, PUT, DELETE)
        'ipAddress', // IP address from which the action was performed e.g 127.0.0.1
        'userAgent', // User agent string of the browser or client used for the action e.g Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0
    ]; // Define fillable attributes for mass assignment
}
 