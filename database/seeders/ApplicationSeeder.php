<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\ApplicationStatusHistory;
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
                'job' => 'مطوّر Laravel أول',
                'status' => 'interview',
                'days_ago' => 6,
                'cover_letter' => 'يسعدني التقدم لهذه الوظيفة؛ لدي خبرة ست سنوات في تطوير تطبيقات Laravel.',
            ],
            [
                'email' => 'seeker@example.com',
                'job' => 'مهندس DevOps',
                'status' => 'applied',
                'days_ago' => 2,
                'cover_letter' => 'لدي خبرة عملية في Docker و Git وأرغب في التوسع في مجال DevOps.',
            ],
            [
                'email' => 'sara@example.com',
                'job' => 'مطوّر واجهات أمامية (React)',
                'status' => 'reviewed',
                'days_ago' => 4,
                'cover_letter' => 'أعمل على React و TypeScript منذ ثلاث سنوات وأحب بناء واجهات نظيفة.',
            ],
            [
                'email' => 'sara@example.com',
                'job' => 'مصمم تجربة وواجهة المستخدم',
                'status' => 'rejected',
                'days_ago' => 10,
                'cover_letter' => 'أجمع بين تصميم الواجهات وتطويرها، وأرغب في العمل مع فريق منتج.',
            ],
            [
                'email' => 'omar@example.com',
                'job' => 'محلل بيانات',
                'status' => 'hired',
                'days_ago' => 20,
                'cover_letter' => 'خبرتي في تحليل البيانات وإعداد التقارير تتوافق مع متطلبات الوظيفة.',
            ],
            [
                'email' => 'omar@example.com',
                'job' => 'مسؤول مبيعات',
                'status' => 'withdraw',
                'days_ago' => 15,
                'cover_letter' => 'أرغب في التركيز على مجال تحليل البيانات حالياً.',
            ],
            [
                'email' => 'test@example.com',
                'job' => 'كاتب تقني',
                'status' => 'applied',
                'days_ago' => 1,
                'cover_letter' => 'أود تجربة عملية التقديم باستخدام هذا الحساب التجريبي.',
            ],
        ];

        // DatabaseSeeder runs WithoutModelEvents, so the observer never records
        // history for seeded applications. Recreate the expected path explicitly.
        $historyPaths = [
            Application::STATUS_APPLIED => [Application::STATUS_APPLIED],
            Application::STATUS_WITHDRAWN => [Application::STATUS_APPLIED, Application::STATUS_WITHDRAWN],
            Application::STATUS_REVIEWED => [Application::STATUS_APPLIED, Application::STATUS_REVIEWED],
            Application::STATUS_REJECTED => [Application::STATUS_APPLIED, Application::STATUS_REJECTED],
            Application::STATUS_INTERVIEW => [Application::STATUS_APPLIED, Application::STATUS_REVIEWED, Application::STATUS_INTERVIEW],
            Application::STATUS_HIRED => [Application::STATUS_APPLIED, Application::STATUS_REVIEWED, Application::STATUS_INTERVIEW, Application::STATUS_HIRED],
        ];

        foreach ($applications as $data) {
            $user = User::query()->where('email', $data['email'])->firstOrFail();
            $job = JobListing::query()->where('title', $data['job'])->firstOrFail();
            $resume = Resume::query()
                ->where('user_id', $user->id)
                ->orderByDesc('is_primary')
                ->first();

            $application = Application::firstOrCreate(
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

            if ($application->statusHistories()->exists()) {
                continue;
            }

            $path = $historyPaths[$application->status];
            $appliedAt = $application->applied_at ?? now();
            $stepHours = count($path) > 1
                ? max(1, intdiv($data['days_ago'] * 24, count($path)))
                : 0;

            $previousStatus = null;

            foreach ($path as $index => $status) {
                $occurredAt = $appliedAt->copy()->addHours($stepHours * $index);

                ApplicationStatusHistory::forceCreate([
                    'application_id' => $application->id,
                    'from_status' => $previousStatus,
                    'to_status' => $status,
                    'created_at' => $occurredAt,
                    'updated_at' => $occurredAt,
                ]);

                $previousStatus = $status;
            }
        }
    }
}
