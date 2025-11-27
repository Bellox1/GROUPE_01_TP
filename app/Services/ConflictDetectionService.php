<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\CourseSession;
use App\Models\Group;
use App\ValueObjects\TimeSlot;
use DateTimeInterface;

class ConflictDetectionService
{
    /**
     * Check for all types of conflicts for a potential session.
     *
     * @param  array{
     *     classroom_id: int,
     *     teacher_id: int,
     *     group_id: int,
     *     start_time: DateTimeInterface|string,
     *     end_time: DateTimeInterface|string,
     *     id?: int|null
     * }  $sessionData
     * @return array<string, array<string, mixed>>
     */
    public function detectConflicts(array $sessionData): array
    {
        $conflicts = [];
        $timeSlot = new TimeSlot($sessionData['start_time'], $sessionData['end_time']);
        $excludeSessionId = $sessionData['id'] ?? null;

        if ($roomConflict = $this->checkRoomConflict($sessionData['classroom_id'], $timeSlot, $excludeSessionId)) {
            $conflicts['room'] = $roomConflict;
        }

        if ($teacherConflict = $this->checkTeacherConflict($sessionData['teacher_id'], $timeSlot, $excludeSessionId)) {
            $conflicts['teacher'] = $teacherConflict;
        }

        if ($groupConflict = $this->checkGroupConflict($sessionData['group_id'], $timeSlot, $excludeSessionId)) {
            $conflicts['group'] = $groupConflict;
        }

        if ($capacityIssue = $this->checkCapacityConstraint($sessionData['classroom_id'], $sessionData['group_id'])) {
            $conflicts['capacity'] = $capacityIssue;
        }

        return $conflicts;
    }

    /**
     * Check if a classroom is available during the specified time.
     *
     * @return array<string, mixed>|null
     */
    public function checkRoomConflict(
        int $classroomId,
        TimeSlot|DateTimeInterface|string $startTimeOrSlot,
        DateTimeInterface|string|int|null $endTimeOrExcludeId = null,
        ?int $excludeSessionId = null
    ): ?array {
        $timeSlot = $this->resolveTimeSlot($startTimeOrSlot, $endTimeOrExcludeId, $excludeSessionId);
        $excludeId = $this->resolveExcludeId($startTimeOrSlot, $endTimeOrExcludeId, $excludeSessionId);

        $conflictingSession = CourseSession::query()
            ->forClassroom($classroomId)
            ->overlappingWithTimeSlot($timeSlot)
            ->excluding($excludeId)
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
     * Check if a teacher is available during the specified time.
     *
     * @return array<string, mixed>|null
     */
    public function checkTeacherConflict(
        int $teacherId,
        TimeSlot|DateTimeInterface|string $startTimeOrSlot,
        DateTimeInterface|string|int|null $endTimeOrExcludeId = null,
        ?int $excludeSessionId = null
    ): ?array {
        $timeSlot = $this->resolveTimeSlot($startTimeOrSlot, $endTimeOrExcludeId, $excludeSessionId);
        $excludeId = $this->resolveExcludeId($startTimeOrSlot, $endTimeOrExcludeId, $excludeSessionId);

        $conflictingSession = CourseSession::query()
            ->forTeacher($teacherId)
            ->overlappingWithTimeSlot($timeSlot)
            ->excluding($excludeId)
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
     * Check if a group is available during the specified time.
     *
     * @return array<string, mixed>|null
     */
    public function checkGroupConflict(
        int $groupId,
        TimeSlot|DateTimeInterface|string $startTimeOrSlot,
        DateTimeInterface|string|int|null $endTimeOrExcludeId = null,
        ?int $excludeSessionId = null
    ): ?array {
        $timeSlot = $this->resolveTimeSlot($startTimeOrSlot, $endTimeOrExcludeId, $excludeSessionId);
        $excludeId = $this->resolveExcludeId($startTimeOrSlot, $endTimeOrExcludeId, $excludeSessionId);

        $conflictingSession = CourseSession::query()
            ->forGroup($groupId)
            ->overlappingWithTimeSlot($timeSlot)
            ->excluding($excludeId)
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
    public function checkCapacityConstraint(int $classroomId, int $groupId): ?array
    {
        $classroom = Classroom::find($classroomId);
        $group = Group::withCount('students')->find($groupId);

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
     * Get all available time slots for a given classroom on a specific date.
     *
     * @return array<int, array{start: string, end: string}>
     */
    public function getAvailableTimeSlots(int $classroomId, string $date, int $durationMinutes = 90): array
    {
        $classroom = Classroom::find($classroomId);
        if (! $classroom) {
            return [];
        }

        $sessions = CourseSession::query()
            ->forClassroom($classroomId)
            ->onDate($date)
            ->orderBy('start_time')
            ->get();

        return $this->calculateAvailableSlots($sessions, $date, $durationMinutes);
    }

    /**
     * Calculate available time slots between existing sessions.
     *
     * @return array<int, array{start: string, end: string}>
     */
    private function calculateAvailableSlots($sessions, string $date, int $durationMinutes): array
    {
        $availableSlots = [];
        $workingHours = [
            'start' => '08:00',
            'end' => '22:00',
        ];

        $currentTime = $date.' '.$workingHours['start'];
        $endTime = $date.' '.$workingHours['end'];
        $durationSeconds = $durationMinutes * 60;

        foreach ($sessions as $session) {
            $sessionStart = $session->start_time instanceof \DateTimeInterface
                ? $session->start_time->format('Y-m-d H:i:s')
                : $session->start_time;

            if (strtotime($currentTime) + $durationSeconds <= strtotime($sessionStart)) {
                $availableSlots[] = [
                    'start' => $currentTime,
                    'end' => date('Y-m-d H:i:s', strtotime($currentTime) + $durationSeconds),
                ];
            }

            $currentTime = $session->end_time instanceof \DateTimeInterface
                ? $session->end_time->format('Y-m-d H:i:s')
                : $session->end_time;
        }

        if (strtotime($currentTime) + $durationSeconds <= strtotime($endTime)) {
            $availableSlots[] = [
                'start' => $currentTime,
                'end' => date('Y-m-d H:i:s', strtotime($currentTime) + $durationSeconds),
            ];
        }

        return $availableSlots;
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
     * Resolve the TimeSlot from flexible method parameters.
     * Supports both (TimeSlot, ?int) and (startTime, endTime, ?int) signatures.
     */
    private function resolveTimeSlot(
        TimeSlot|DateTimeInterface|string $startTimeOrSlot,
        DateTimeInterface|string|int|null $endTimeOrExcludeId,
        ?int $excludeSessionId
    ): TimeSlot {
        if ($startTimeOrSlot instanceof TimeSlot) {
            return $startTimeOrSlot;
        }

        if (is_int($endTimeOrExcludeId) || $endTimeOrExcludeId === null) {
            throw new \InvalidArgumentException('When using start_time/end_time signature, end_time is required.');
        }

        return new TimeSlot($startTimeOrSlot, $endTimeOrExcludeId);
    }

    /**
     * Resolve the exclude session ID from flexible method parameters.
     */
    private function resolveExcludeId(
        TimeSlot|DateTimeInterface|string $startTimeOrSlot,
        DateTimeInterface|string|int|null $endTimeOrExcludeId,
        ?int $excludeSessionId
    ): ?int {
        if ($startTimeOrSlot instanceof TimeSlot) {
            return is_int($endTimeOrExcludeId) ? $endTimeOrExcludeId : null;
        }

        return $excludeSessionId;
    }
}
