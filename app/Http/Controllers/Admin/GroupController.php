<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $groups = Group::withCount('students')->get();

        return view('admin.groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.groups.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:groups,name',
        ]);

        Group::create($validated);

        return redirect()->route('admin.groups.index')
            ->with('success', 'Group created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Group $group)
    {
        return view('admin.groups.edit', compact('group'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:groups,name,'.$group->id,
        ]);

        $group->update($validated);

        return redirect()->route('admin.groups.index')
            ->with('success', 'Group updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        $group->delete();

        return redirect()->route('admin.groups.index')
            ->with('success', 'Group deleted successfully.');
    }
}
