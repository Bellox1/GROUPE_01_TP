<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Classroom>
 */
class ClassroomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->bothify('Room ###'),
            'capacity' => $this->faker->numberBetween(20, 200),
            'type' => $this->faker->randomElement(['Amphitheater', 'ClassRoom', 'Lab']),
        ];
    }
}
