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

    public function createSession(array $data): CourseSession
    {
        $this->validateSessionData($data);
        $this->validateQuota($data);
        $this->checkForConflicts($data);

        return CourseSession::create($data);
    }

    /**
     * Validate course hourly quota.
     */
    private function validateQuota(array $data): void
    {
        $course = \App\Models\Course::findOrFail($data['course_id']);
        
        $type = strtoupper($data['type']);
        $quotaField = match($type) {
            'CM' => 'quota_cm',
            'TD' => 'quota_td',
            'TP' => 'quota_tp',
            default => null
        };

        if (!$quotaField) {
            return;
        }

        $quota = $course->$quotaField;
        
        $existingSessions = CourseSession::where('course_id', $course->id)
            ->where('type', $data['type'])
            ->get();

        $totalMinutes = $existingSessions->reduce(function ($carry, $session) {
            return $carry + $session->start_time->diffInMinutes($session->end_time);
        }, 0);

        $newSessionMinutes = \Carbon\Carbon::parse($data['start_time'])->diffInMinutes(\Carbon\Carbon::parse($data['end_time']));
        
        if (($totalMinutes + $newSessionMinutes) / 60 > $quota) {
            throw new \InvalidArgumentException('Quota reached for this type of session.');
        }
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
