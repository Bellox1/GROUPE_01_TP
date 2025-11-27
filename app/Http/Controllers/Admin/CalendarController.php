<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\CourseSession;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $query = CourseSession::with(['course', 'classroom', 'group']);

        if ($request->filled('group')) {
            $query->where('group_id', $request->group);
        }

        if ($request->filled('teacher')) {
            $query->where('teacher_id', $request->teacher);
        }

        if ($request->filled('classroom')) {
            $query->where('classroom_id', $request->classroom);
        }

        $sessions = $query->get();

        $groups = Group::all();
        $teachers = User::where('role', 'teacher')->get();
        $classrooms = Classroom::all();

        return view('admin.calendar.index', compact('sessions', 'groups', 'teachers', 'classrooms'));
    }
}
