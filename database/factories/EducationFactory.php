<?php

namespace Database\Factories;

use App\Models\Education;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Education>
 */
class EducationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-8 years', '-4 years');
        $endedAt = fake()->dateTimeBetween($startedAt, '-1 year');

        return [
            'user_id' => User::factory(),
            'degree' => fake()->randomElement(['بكالوريوس', 'ماجستير', 'دبلوم']),
            'institution' => fake()->company().' University',
            'field_of_study' => fake()->randomElement(['علوم الحاسب', 'هندسة البرمجيات', 'نظم المعلومات']),
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'is_current' => false,
            'description' => fake()->optional()->sentence(10),
        ];
    }
}
