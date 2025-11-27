<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Services\TeacherAssignmentService;
use Illuminate\Http\Request;

class TeacherAssignmentController extends Controller
{
    public function __construct(
        private TeacherAssignmentService $teacherAssignmentService
    ) {}

    /**
     * Display the teacher assignment form for a course.
     */
    public function edit(Course $course)
    {
        $assignedTeachers = $this->teacherAssignmentService->getCourseTeachers($course);
        $availableTeachers = $this->teacherAssignmentService->getAvailableTeachers()
            ->reject(function ($teacher) use ($assignedTeachers) {
                return $assignedTeachers->contains('id', $teacher->id);
            });

        return view('admin.courses.teachers', compact('course', 'assignedTeachers', 'availableTeachers'));
    }

    /**
     * Assign a teacher to a course.
     */
    public function assign(Request $request, Course $course)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
        ]);

        $teacher = User::findOrFail($request->teacher_id);

        try {
            $this->teacherAssignmentService->assignTeacher($course, $teacher);

            return redirect()->route('admin.courses.teachers.edit', $course)
                ->with('success', 'Teacher assigned successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove a teacher from a course.
     */
    public function remove(Course $course, User $teacher)
    {
        $this->teacherAssignmentService->removeTeacher($course, $teacher);

        return redirect()->route('admin.courses.teachers.edit', $course)
            ->with('success', 'Teacher removed successfully.');
    }
}
