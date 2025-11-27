<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Group;
use App\Models\User;
use App\Services\SessionCreationService;
use Illuminate\Http\Request;

class CourseSessionController extends Controller
{
    public function __construct(
        private SessionCreationService $sessionCreationService
    ) {}

    /**
     * Display a listing of course sessions.
     */
    public function index()
    {
        $sessions = CourseSession::with(['course', 'classroom', 'teacher', 'group'])
            ->orderBy('start_time', 'desc')
            ->get();

        return view('admin.sessions.index', compact('sessions'));
    }

    /**
     * Show the form for creating a new course session.
     */
    public function create()
    {
        $courses = Course::all();
        $classrooms = Classroom::all();
        $teachers = User::where('role', User::ROLE_TEACHER)->get();
        $groups = Group::all();

        return view('admin.sessions.create', compact('courses', 'classrooms', 'teachers', 'groups'));
    }

    /**
     * Store a newly created course session in storage.
     */
    public function store(Request $request)
    {
        try {
            $sessionData = $request->validate([
                'course_id' => 'required|exists:courses,id',
                'classroom_id' => 'required|exists:classrooms,id',
                'teacher_id' => 'required|exists:users,id',
                'group_id' => 'required|exists:groups,id',
                'start_time' => 'required|date|after:now',
                'end_time' => 'required|date|after:start_time',
                'type' => 'required|in:lecture,lab,seminar',
            ]);

            $session = $this->sessionCreationService->createSession($sessionData);

            return redirect()->route('admin.sessions.index')
                ->with('success', 'Course session created successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified course session.
     */
    public function show(CourseSession $session)
    {
        $session->load(['course', 'classroom', 'teacher', 'group']);

        return view('admin.sessions.show', compact('session'));
    }

    /**
     * Show the form for editing the specified course session.
     */
    public function edit(CourseSession $session)
    {
        $courses = Course::all();
        $classrooms = Classroom::all();
        $teachers = User::where('role', User::ROLE_TEACHER)->get();
        $groups = Group::all();

        return view('admin.sessions.edit', compact('session', 'courses', 'classrooms', 'teachers', 'groups'));
    }

    /**
     * Update the specified course session in storage.
     */
    public function update(Request $request, CourseSession $session)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'teacher_id' => 'required|exists:users,id',
            'group_id' => 'required|exists:groups,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'type' => 'required|in:lecture,lab,seminar',
        ]);

        $session->update($validated);

        return redirect()->route('admin.sessions.index')
            ->with('success', 'Course session updated successfully.');
    }

    /**
     * Remove the specified course session from storage.
     */
    public function destroy(CourseSession $session)
    {
        $session->delete();

        return redirect()->route('admin.sessions.index')
            ->with('success', 'Course session deleted successfully.');
    }
}
