<?php

namespace App\Models\Traits;

use App\Models\CourseSession;
use App\ValueObjects\TimeSlot;
use Illuminate\Database\Eloquent\Builder;

trait HasConflictDetection
{
    /**
     * Check if this session conflicts with any existing sessions.
     *
     * @return array<string, array<string, mixed>>
     */
    public function detectConflicts(): array
    {
        $conflicts = [];
        $timeSlot = $this->getTimeSlot();

        if ($roomConflict = $this->checkRoomConflict($timeSlot)) {
            $conflicts['room'] = $roomConflict;
        }

        if ($teacherConflict = $this->checkTeacherConflict($timeSlot)) {
            $conflicts['teacher'] = $teacherConflict;
        }

        if ($groupConflict = $this->checkGroupConflict($timeSlot)) {
            $conflicts['group'] = $groupConflict;
        }

        if ($capacityIssue = $this->checkCapacityConstraint()) {
            $conflicts['capacity'] = $capacityIssue;
        }

        return $conflicts;
    }

    /**
     * Check if this session conflicts with a room booking.
     *
     * @return array<string, mixed>|null
     */
    public function checkRoomConflict(TimeSlot $timeSlot): ?array
    {
        $conflictingSession = CourseSession::query()
            ->forClassroom($this->classroom_id)
            ->overlappingWithTimeSlot($timeSlot)
            ->excluding($this->id)
            ->first();

        if (! $conflictingSession) {
            return null;
        }

        return $this->buildConflictResponse(
            'room_conflict',
            "Classroom '{$conflictingSession->classroom->name}' is already booked",
            $conflictingSession
        );
    }

    /**
     * Check if this session conflicts with teacher availability.
     *
     * @return array<string, mixed>|null
     */
    public function checkTeacherConflict(TimeSlot $timeSlot): ?array
    {
        $conflictingSession = CourseSession::query()
            ->forTeacher($this->teacher_id)
            ->overlappingWithTimeSlot($timeSlot)
            ->excluding($this->id)
            ->first();

        if (! $conflictingSession) {
            return null;
        }

        return $this->buildConflictResponse(
            'teacher_conflict',
            "Teacher '{$conflictingSession->teacher->name}' is already teaching",
            $conflictingSession
        );
    }

    /**
     * Check if this session conflicts with group schedule.
     *
     * @return array<string, mixed>|null
     */
    public function checkGroupConflict(TimeSlot $timeSlot): ?array
    {
        $conflictingSession = CourseSession::query()
            ->forGroup($this->group_id)
            ->overlappingWithTimeSlot($timeSlot)
            ->excluding($this->id)
            ->first();

        if (! $conflictingSession) {
            return null;
        }

        return $this->buildConflictResponse(
            'group_conflict',
            "Group '{$conflictingSession->group->name}' already has a class",
            $conflictingSession
        );
    }

    /**
     * Check if classroom capacity can accommodate the group.
     *
     * @return array<string, mixed>|null
     */
    public function checkCapacityConstraint(): ?array
    {
        $classroom = $this->classroom;
        $group = $this->group->loadCount('students');

        if (! $classroom || ! $group) {
            return null;
        }

        $studentCount = $group->students_count;
        $capacity = $classroom->capacity;

        if ($studentCount > $capacity) {
            return [
                'type' => 'capacity_constraint',
                'message' => "Classroom capacity ({$capacity}) is insufficient for group size ({$studentCount})",
                'classroom_capacity' => $capacity,
                'group_size' => $studentCount,
                'overflow' => $studentCount - $capacity,
            ];
        }

        return null;
    }

    /**
     * Check if this session has any conflicts at all.
     */
    public function hasConflicts(): bool
    {
        return ! empty($this->detectConflicts());
    }

    /**
     * Get the first conflict found, if any.
     *
     * @return array<string, mixed>|null
     */
    public function getFirstConflict(): ?array
    {
        $conflicts = $this->detectConflicts();

        return $conflicts ? reset($conflicts) : null;
    }

    /**
     * Build a standardized conflict response array.
     *
     * @return array<string, mixed>
     */
    private function buildConflictResponse(string $type, string $message, CourseSession $conflictingSession): array
    {
        return [
            'type' => $type,
            'message' => $message,
            'conflicting_session' => $conflictingSession,
            'conflict_time' => [
                'start' => $conflictingSession->start_time->format('M j, Y g:i A'),
                'end' => $conflictingSession->end_time->format('M j, Y g:i A'),
            ],
        ];
    }

    /**
     * Get all conflicting sessions for this session.
     */
    public function getConflictingSessions(): \Illuminate\Database\Eloquent\Collection
    {
        $timeSlot = $this->getTimeSlot();

        return CourseSession::query()
            ->where(function (Builder $query) {
                $query->forClassroom($this->classroom_id)
                    ->orWhere->forTeacher($this->teacher_id)
                    ->orWhere->forGroup($this->group_id);
            })
            ->overlappingWithTimeSlot($timeSlot)
            ->excluding($this->id)
            ->get();
    }

    /**
     * Validate that this session has no conflicts.
     *
     * @throws \RuntimeException
     */
    public function validateNoConflicts(): void
    {
        $conflicts = $this->detectConflicts();

        if (! empty($conflicts)) {
            $firstConflict = reset($conflicts);
            throw new \RuntimeException($firstConflict['message']);
        }
    }
}
