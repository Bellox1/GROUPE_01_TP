<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;

class ScheduleController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $sessions = CourseSession::where('teacher_id', $user->id)
            ->with(['course', 'classroom', 'group'])
            ->get();

        return view('teacher.schedule.index', compact('sessions'));
    }
}
