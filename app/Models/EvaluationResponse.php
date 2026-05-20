<?php
// Updated EvaluationResponse Model with Cooldown Support
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EvaluationResponse extends Model
{
    protected $fillable = [
        'evaluation_id',
        'schedule_id',
        'ip_address',
        'effectiveness_rating',
        'feedback_comments',
        'course_code_snapshot',
        'course_name_snapshot',
        'schedule_time_snapshot',
        'schedule_days_snapshot',
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function getEffectivenessTextAttribute()
    {
        $ratings = [
            '1' => 'Not Effective',
            '2' => 'Somewhat Effective',
            '3' => 'Effective',
            '4' => 'Very Effective'
        ];

        return $ratings[$this->effectiveness_rating] ?? 'Unknown';
    }

    public function getResolvedCourseCodeAttribute(): string
    {
        $course = optional(optional($this->schedule)->facultyCourse)->course;
        return $course->class_code ?? $this->course_code_snapshot ?? 'N/A';
    }

    public function getResolvedCourseNameAttribute(): string
    {
        $course = optional(optional($this->schedule)->facultyCourse)->course;
        return $course->subject_code ?? $this->course_name_snapshot ?? 'N/A';
    }

    public function getResolvedScheduleTimeAttribute(): ?string
    {
        return optional($this->schedule)->time ?? $this->schedule_time_snapshot;
    }

    public function getResolvedScheduleDaysAttribute(): ?string
    {
        return optional($this->schedule)->day ?? $this->schedule_days_snapshot;
    }

    /**
     * Check if IP address is still in cooldown period for a specific schedule
     */
    public static function isInCooldown($scheduleId, $ipAddress, $cooldownMinutes = 1)
    {
        $lastSubmission = self::where('schedule_id', $scheduleId)
            ->where('ip_address', $ipAddress)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastSubmission) {
            return false;
        }

        $cooldownUntil = $lastSubmission->created_at->addMinutes($cooldownMinutes);
        return Carbon::now()->lt($cooldownUntil);
    }

    /**
     * Get remaining cooldown time in seconds
     */
    public static function getRemainingCooldown($scheduleId, $ipAddress, $cooldownMinutes = 1)
    {
        $lastSubmission = self::where('schedule_id', $scheduleId)
            ->where('ip_address', $ipAddress)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastSubmission) {
            return 0;
        }

        $cooldownUntil = $lastSubmission->created_at->addMinutes($cooldownMinutes);
        return Carbon::now()->lt($cooldownUntil) ? Carbon::now()->diffInSeconds($cooldownUntil) : 0;
    }
}
