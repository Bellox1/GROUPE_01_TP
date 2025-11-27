<?php

use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CourseSessionController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\TeacherAssignmentController;
use App\Http\Controllers\Student\ScheduleController;
use App\Http\Controllers\Teacher\ScheduleController as TeacherScheduleController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    // Student Routes
    Route::get('student/schedule', [ScheduleController::class, 'index'])->name('student.schedule');

    // Teacher Routes
    Route::get('teacher/schedule', [TeacherScheduleController::class, 'index'])->name('teacher.schedule');

    // Admin Routes
    Route::get('admin/calendar', [CalendarController::class, 'index'])->name('admin.calendar.index');
    Route::resource('admin/classrooms', ClassroomController::class)->names('admin.classrooms');
    Route::resource('admin/courses', CourseController::class)->names('admin.courses');
    Route::resource('admin/groups', GroupController::class)->names('admin.groups');
    Route::resource('admin/sessions', CourseSessionController::class)->names('admin.sessions');

    // Teacher Assignment Routes
    Route::get('admin/courses/{course}/teachers', [TeacherAssignmentController::class, 'edit'])->name('admin.courses.teachers.edit');
    Route::post('admin/courses/{course}/teachers/assign', [TeacherAssignmentController::class, 'assign'])->name('admin.courses.teachers.assign');
    Route::delete('admin/courses/{course}/teachers/{teacher}', [TeacherAssignmentController::class, 'remove'])->name('admin.courses.teachers.remove');
});
