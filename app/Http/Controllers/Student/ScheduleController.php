<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class ScheduleController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $groupIds = $user->groups()->pluck('id');

        $sessions = CourseSession::whereIn('group_id', $groupIds)
            ->with(['course', 'classroom', 'group'])
            ->get();

        return view('student.schedule.index', compact('sessions'));
    }
}
