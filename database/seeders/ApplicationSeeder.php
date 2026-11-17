<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\JobListing;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $applications = [
            [
                'email' => 'seeker@example.com',
                'job' => 'Senior Laravel Developer',
                'status' => 'interview',
                'days_ago' => 6,
                'cover_letter' => 'يسعدني التقدم لهذه الوظيفة؛ لدي خبرة ست سنوات في تطوير تطبيقات Laravel.',
            ],
            [
                'email' => 'seeker@example.com',
                'job' => 'DevOps Engineer',
                'status' => 'applied',
                'days_ago' => 2,
                'cover_letter' => 'لدي خبرة عملية في Docker و Git وأرغب في التوسع في مجال DevOps.',
            ],
            [
                'email' => 'sara@example.com',
                'job' => 'Frontend Developer (React)',
                'status' => 'reviewed',
                'days_ago' => 4,
                'cover_letter' => 'أعمل على React و TypeScript منذ ثلاث سنوات وأحب بناء واجهات نظيفة.',
            ],
            [
                'email' => 'sara@example.com',
                'job' => 'UI/UX Designer',
                'status' => 'rejected',
                'days_ago' => 10,
                'cover_letter' => 'أجمع بين تصميم الواجهات وتطويرها، وأرغب في العمل مع فريق منتج.',
            ],
            [
                'email' => 'omar@example.com',
                'job' => 'Data Analyst',
                'status' => 'hired',
                'days_ago' => 20,
                'cover_letter' => 'خبرتي في تحليل البيانات وإعداد التقارير تتوافق مع متطلبات الوظيفة.',
            ],
            [
                'email' => 'omar@example.com',
                'job' => 'Sales Executive',
                'status' => 'withdraw',
                'days_ago' => 15,
                'cover_letter' => 'أرغب في التركيز على مجال تحليل البيانات حالياً.',
            ],
            [
                'email' => 'test@example.com',
                'job' => 'Technical Writer',
                'status' => 'applied',
                'days_ago' => 1,
                'cover_letter' => 'أود تجربة عملية التقديم باستخدام هذا الحساب التجريبي.',
            ],
        ];

        foreach ($applications as $data) {
            $user = User::query()->where('email', $data['email'])->firstOrFail();
            $job = JobListing::query()->where('title', $data['job'])->firstOrFail();
            $resume = Resume::query()
                ->where('user_id', $user->id)
                ->orderByDesc('is_primary')
                ->first();

            Application::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'job_listing_id' => $job->id,
                ],
                [
                    'resume_id' => $resume?->id,
                    'status' => $data['status'],
                    'cover_letter' => $data['cover_letter'],
                    'applied_at' => now()->subDays($data['days_ago']),
                ],
            );
        }
    }
}
