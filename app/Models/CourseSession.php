<?php

namespace App\Models;

use App\Models\Traits\HasConflictDetection;
use App\ValueObjects\TimeSlot;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseSession extends Model
{
    use HasConflictDetection;

    /** @use HasFactory<\Database\Factories\CourseSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'course_id',
        'classroom_id',
        'teacher_id',
        'group_id',
        'start_time',
        'end_time',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    /**
     * Get the TimeSlot value object for this session.
     */
    public function getTimeSlot(): TimeSlot
    {
        return TimeSlot::fromModel($this);
    }

    /**
     * Scope to find sessions that overlap with a given time range.
     * Two time ranges overlap if one starts before the other ends AND ends after the other starts.
     */
    public function scopeOverlappingWith(Builder $query, DateTimeInterface|string $startTime, DateTimeInterface|string $endTime): Builder
    {
        return $query->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);
    }

    /**
     * Scope to find sessions overlapping with a TimeSlot.
     */
    public function scopeOverlappingWithTimeSlot(Builder $query, TimeSlot $timeSlot): Builder
    {
        return $query->overlappingWith($timeSlot->startTime, $timeSlot->endTime);
    }

    /**
     * Scope to find sessions for a specific classroom.
     */
    public function scopeForClassroom(Builder $query, int $classroomId): Builder
    {
        return $query->where('classroom_id', $classroomId);
    }

    /**
     * Scope to find sessions for a specific teacher.
     */
    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope to find sessions for a specific group.
     */
    public function scopeForGroup(Builder $query, int $groupId): Builder
    {
        return $query->where('group_id', $groupId);
    }

    /**
     * Scope to exclude a specific session (useful when updating).
     */
    public function scopeExcluding(Builder $query, ?int $sessionId): Builder
    {
        if ($sessionId !== null) {
            return $query->where('id', '!=', $sessionId);
        }

        return $query;
    }

    /**
     * Scope to find sessions on a specific date.
     */
    public function scopeOnDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('start_time', $date);
    }

    /**
     * Check if this session overlaps with a given time range.
     */
    public function overlapsWithTimeRange(DateTimeInterface|string $startTime, DateTimeInterface|string $endTime): bool
    {
        $otherTimeSlot = new TimeSlot($startTime, $endTime);

        return $this->getTimeSlot()->overlapsWith($otherTimeSlot);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
