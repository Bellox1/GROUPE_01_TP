<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;

class TeacherAssignmentService
{
    /**
     * Assign a teacher to a course.
     */
    public function assignTeacher(Course $course, User $teacher): void
    {
        if ($teacher->role !== User::ROLE_TEACHER) {
            throw new \InvalidArgumentException('Only teachers can be assigned to courses.');
        }

        // Check if already assigned to avoid duplicate constraint error
        if (! $this->isTeacherAssigned($course, $teacher)) {
            $course->teachers()->attach($teacher->id);
        }
    }

    /**
     * Remove a teacher from a course.
     */
    public function removeTeacher(Course $course, User $teacher): void
    {
        $course->teachers()->detach($teacher->id);
    }

    /**
     * Get all available teachers (users with teacher role).
     */
    public function getAvailableTeachers(): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('role', User::ROLE_TEACHER)->get();
    }

    /**
     * Get teachers assigned to a specific course.
     */
    public function getCourseTeachers(Course $course): \Illuminate\Database\Eloquent\Collection
    {
        return $course->teachers()->get();
    }

    /**
     * Get courses taught by a specific teacher.
     */
    public function getTeacherCourses(User $teacher): \Illuminate\Database\Eloquent\Collection
    {
        return $teacher->courses()->get();
    }

    /**
     * Check if a teacher is assigned to a course.
     */
    public function isTeacherAssigned(Course $course, User $teacher): bool
    {
        return $course->teachers()->where('teacher_id', $teacher->id)->exists();
    }
}
