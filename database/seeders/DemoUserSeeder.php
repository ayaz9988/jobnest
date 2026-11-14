<?php

namespace Database\Seeders;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Acme Technologies',
                'email' => 'employer@example.com',
                'role' => 'employer',
                'location' => 'Dubai, UAE',
                'profile' => 'شركة تقنية متخصصة في تطوير تطبيقات الويب والخدمات السحابية.',
                'skills' => [],
            ],
            [
                'name' => 'Nova Digital',
                'email' => 'employer2@example.com',
                'role' => 'employer',
                'location' => 'Riyadh, Saudi Arabia',
                'profile' => 'وكالة رقمية تعمل على التصميم والتسويق وتحليل البيانات.',
                'skills' => [],
            ],
            [
                'name' => 'أحمد خليل',
                'email' => 'seeker@example.com',
                'role' => 'seeker',
                'location' => 'Dubai, UAE',
                'profile' => 'مطور ويب بخبرة 6 سنوات في Laravel و PHP و MySQL.',
                'skills' => [
                    'Laravel' => ['advanced', 6],
                    'PHP' => ['advanced', 6],
                    'MySQL' => ['intermediate', 4],
                    'Docker' => ['intermediate', 2],
                    'Git' => ['advanced', 5],
                ],
            ],
            [
                'name' => 'سارة أحمد',
                'email' => 'sara@example.com',
                'role' => 'seeker',
                'location' => 'Amman, Jordan',
                'profile' => 'مصممة واجهات ومطورة فرونت إند مهتمة بتجربة المستخدم.',
                'skills' => [
                    'JavaScript' => ['advanced', 4],
                    'React' => ['advanced', 3],
                    'TypeScript' => ['intermediate', 3],
                    'Tailwind CSS' => ['advanced', 4],
                    'UI/UX Design' => ['intermediate', 2],
                ],
            ],
            [
                'name' => 'عمر خالد',
                'email' => 'omar@example.com',
                'role' => 'seeker',
                'location' => 'Riyadh, Saudi Arabia',
                'profile' => 'محلل بيانات يجيد Python و SQL وإعداد لوحات المعلومات.',
                'skills' => [
                    'Python' => ['intermediate', 3],
                    'Data Analysis' => ['advanced', 4],
                    'PostgreSQL' => ['intermediate', 3],
                    'Git' => ['intermediate', 3],
                ],
            ],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'role' => 'seeker',
                'location' => 'Remote',
                'profile' => 'حساب تجريبي للاختبار المحلي.',
                'skills' => [
                    'PHP' => ['beginner', 1],
                    'Git' => ['beginner', 1],
                ],
            ],
        ];

        foreach ($users as $attributes) {
            $skills = $attributes['skills'];
            unset($attributes['skills']);

            $user = User::updateOrCreate(
                ['email' => $attributes['email']],
                [
                    ...$attributes,
                    'password' => 'password',
                    'email_verified_at' => now(),
                ],
            );

            $skillIds = Skill::query()
                ->whereIn('name', array_keys($skills))
                ->pluck('id', 'name');

            $pivot = [];

            foreach ($skills as $name => [$proficiency, $years]) {
                if ($skillIds->has($name)) {
                    $pivot[$skillIds[$name]] = [
                        'proficiency' => $proficiency,
                        'years_of_experience' => $years,
                    ];
                }
            }

            if ($pivot !== []) {
                $user->skills()->syncWithoutDetaching($pivot);
            }
        }
    }
}
