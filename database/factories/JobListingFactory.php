<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobListing>
 */
class JobListingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'posted_by' => User::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->jobTitle(),
            'description' => fake()->paragraphs(3, true),
            'employment_type' => fake()->randomElement([
                'full_time',
                'part_time',
                'contract',
                'internship',
                'temporary',
            ]),
            'workplace_type' => fake()->randomElement(['on_site', 'remote', 'hybrid']),
            'location' => fake()->city(),
            'salary_min' => fake()->numberBetween(1_000, 5_000),
            'salary_max' => fake()->numberBetween(5_000, 15_000),
            'salary_currency' => 'USD',
            'status' => 'open',
            'expires_at' => now()->addMonth(),
            'published_at' => now(),
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }
}
