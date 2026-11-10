<?php

namespace Database\Factories;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Skill>
 */
class SkillFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'PHP',
                'Laravel',
                'JavaScript',
                'TypeScript',
                'React',
                'Vue.js',
                'Node.js',
                'MySQL',
                'PostgreSQL',
                'Docker',
                'Git',
                'Tailwind CSS',
                'UI/UX Design',
                'Project Management',
                'Python',
                'Data Analysis',
            ]),
        ];
    }
}
