<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [];

        for ($level = 1; $level <= 3; $level++) {
            for ($group = 1; $group <= 6; $group++) {
                $groups[] = ['name' => "L{$level}-G{$group}"];
            }
        }

        foreach ($groups as $group) {
            Group::create($group);
        }

        $allGroups = Group::all();
        $students = User::where('role', 'student')->get();

        $studentsPerGroup = intdiv($students->count(), $allGroups->count());
        $extraStudents = $students->count() % $allGroups->count();

        $studentIndex = 0;

        foreach ($allGroups as $group) {
            $numStudents = $studentsPerGroup + ($extraStudents > 0 ? 1 : 0);
            $extraStudents--;

            for ($i = 0; $i < $numStudents; $i++) {
                if ($studentIndex < $students->count()) {
                    $group->students()->attach($students[$studentIndex]->id);
                    $studentIndex++;
                }
            }
        }
    }
}
