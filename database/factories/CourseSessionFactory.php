<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Course;
use App\Models\Classroom;
use App\Models\Group;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseSession>
 */
class CourseSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('next Monday', 'next Monday + 7 days');
        $start->setTime($this->faker->numberBetween(8, 18), 0, 0);
        $end = (clone $start)->modify('+2 hours');

        return [
            'course_id' => Course::factory(),
            'classroom_id' => Classroom::factory(),
            'teacher_id' => User::factory()->teacher(),
            'group_id' => Group::factory(),
            'start_time' => $start,
            'end_time' => $end,
            'type' => $this->faker->randomElement(['CM', 'TD', 'TP']),
        ];
    }
}
