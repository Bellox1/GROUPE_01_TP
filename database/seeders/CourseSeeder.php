<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            ['code' => 'ENG101', 'name' => 'English', 'description' => 'English language course for communication skills', 'credits' => 3],
            ['code' => 'C101', 'name' => 'C Programming', 'description' => 'Introduction to C programming language', 'credits' => 4],
            ['code' => 'SALES101', 'name' => 'Sales Techniques', 'description' => 'Fundamentals of sales and customer relations', 'credits' => 3],
            ['code' => 'MATH101', 'name' => 'Mathematics', 'description' => 'Basic mathematics for business applications', 'credits' => 3],
            ['code' => 'ACCT101', 'name' => 'Accounting', 'description' => 'Principles of financial accounting', 'credits' => 4],
            ['code' => 'MKT101', 'name' => 'Marketing', 'description' => 'Introduction to marketing strategies', 'credits' => 3],
            ['code' => 'MGMT101', 'name' => 'Management', 'description' => 'Business management fundamentals', 'credits' => 3],
            ['code' => 'ECON101', 'name' => 'Economics', 'description' => 'Basic economic principles', 'credits' => 3],
            ['code' => 'FIN101', 'name' => 'Finance', 'description' => 'Introduction to corporate finance', 'credits' => 3],
            ['code' => 'STAT101', 'name' => 'Statistics', 'description' => 'Statistical analysis for business', 'credits' => 3],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }

        Course::factory(5)->create();

        $teachers = User::where('role', 'teacher')->get();
        $allCourses = Course::all();

        foreach ($teachers as $teacher) {
            $coursesToTeach = $allCourses->random(rand(1, 3));
            $teacher->courses()->attach($coursesToTeach);
        }
    }
}
