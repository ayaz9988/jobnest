<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * @var array<string, string>
     */
    private const RENAMES = [
        'UI/UX Design' => 'تصميم واجهات وتجارب المستخدم',
        'Project Management' => 'إدارة المشاريع',
        'Data Analysis' => 'تحليل البيانات',
        'Digital Marketing' => 'التسويق الرقمي',
        'Sales' => 'المبيعات',
        'Customer Support' => 'دعم العملاء',
        'Technical Writing' => 'الكتابة التقنية',
        'English' => 'اللغة الإنجليزية',
        'Arabic Writing' => 'الكتابة بالعربية',
    ];

    public function run(): void
    {
        // Renaming in place keeps existing user and listing pivots attached.
        foreach (self::RENAMES as $from => $to) {
            if (! Skill::query()->where('name', $to)->exists()) {
                Skill::query()->where('name', $from)->update(['name' => $to]);
            }
        }

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
            ...array_values(self::RENAMES),
        ];

        foreach ($skills as $name) {
            Skill::firstOrCreate(['name' => $name]);
        }
    }
}
