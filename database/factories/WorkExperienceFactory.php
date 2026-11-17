<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkExperience;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkExperience>
 */
class WorkExperienceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-10 years', '-1 year');
        $endedAt = fake()->dateTimeBetween($startedAt, 'now');
        $isCurrent = fake()->boolean(20);

        return [
            'user_id' => User::factory(),
            'job_title' => fake()->jobTitle(),
            'company' => fake()->company(),
            'location' => fake()->city(),
            'started_at' => $startedAt,
            'ended_at' => $isCurrent ? null : $endedAt,
            'is_current' => $isCurrent,
            'description' => fake()->optional()->sentence(12),
        ];
    }

    public function current(): static
    {
        return $this->state(fn (array $attributes): array => [
            'ended_at' => null,
            'is_current' => true,
        ]);
    }
}
