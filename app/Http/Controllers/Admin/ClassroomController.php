<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $classrooms = Classroom::all();
        return view('admin.classrooms.index', compact('classrooms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.classrooms.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'type' => 'required|string|in:Amphitheater,ClassRoom,Lab',
        ]);

        Classroom::create($validated);

        return redirect()->route('admin.classrooms.index')
            ->with('success', 'Classroom created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Classroom $classroom)
    {
        return view('admin.classrooms.edit', compact('classroom'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'type' => 'required|string|in:Amphitheater,ClassRoom,Lab',
        ]);

        $classroom->update($validated);

        return redirect()->route('admin.classrooms.index')
            ->with('success', 'Classroom updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Classroom $classroom)
    {
        $classroom->delete();

        return redirect()->route('admin.classrooms.index')
            ->with('success', 'Classroom deleted successfully.');
    }
}
