<?php

namespace App\Services;

use App\Models\CourseSession;
use App\Models\User;

class SessionCreationService
{
    private ConflictDetectionService $conflictDetectionService;

    public function __construct(ConflictDetectionService $conflictDetectionService)
    {
        $this->conflictDetectionService = $conflictDetectionService;
    }

    /**
     * Create a new course session with validation.
     */
    public function createSession(array $data): CourseSession
    {
        $this->validateSessionData($data);
        $this->checkForConflicts($data);

        return CourseSession::create($data);
    }

    /**
     * Validate session data before creation.
     */
    private function validateSessionData(array $data): void
    {
        $requiredFields = ['course_id', 'classroom_id', 'teacher_id', 'group_id', 'start_time', 'end_time', 'type'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (! isset($data[$field]) || $data[$field] === '') {
                $missingFields[] = $field;
            }
        }

        if (! empty($missingFields)) {
            throw new \InvalidArgumentException('Missing required fields: '.implode(', ', $missingFields));
        }

        // Validate end time is after start time
        if ($data['end_time'] <= $data['start_time']) {
            throw new \InvalidArgumentException('End time must be after start time.');
        }

        // Validate teacher role
        $teacher = User::find($data['teacher_id']);
        if (! $teacher || $teacher->role !== User::ROLE_TEACHER) {
            throw new \InvalidArgumentException('Assigned user must be a teacher.');
        }
    }

    /**
     * Check for scheduling conflicts.
     */
    private function checkForConflicts(array $data): void
    {
        $conflicts = $this->conflictDetectionService->detectConflicts($data);

        if (! empty($conflicts)) {
            $conflictMessages = [];
            foreach ($conflicts as $conflict) {
                $conflictMessages[] = $conflict['message'];
            }
            throw new \InvalidArgumentException('Scheduling conflicts detected: '.implode('; ', $conflictMessages));
        }
    }
}
