<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            'PHP',
            'Laravel',
            'JavaScript',
            'TypeScript',
            'React',
            'Vue.js',
            'Node.js',
            'Python',
            'MySQL',
            'PostgreSQL',
            'REST APIs',
            'Docker',
            'Git',
            'Tailwind CSS',
            'UI/UX Design',
            'Project Management',
            'Data Analysis',
            'Digital Marketing',
            'Sales',
            'Customer Support',
            'Technical Writing',
            'English',
            'Arabic Writing',
        ];

        foreach ($skills as $name) {
            Skill::firstOrCreate(['name' => $name]);
        }
    }
}
