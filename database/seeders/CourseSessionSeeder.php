<?php

namespace Database\Seeders;

use App\Models\CourseSession;
use Illuminate\Database\Seeder;

class CourseSessionSeeder extends Seeder
{
    public function run(): void
    {
        CourseSession::factory(20)->create();
    }
}
