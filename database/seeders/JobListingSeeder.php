<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\JobListing;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;

class JobListingSeeder extends Seeder
{
    public function run(): void
    {
        $listings = [
            [
                'employer' => 'employer@example.com',
                'category' => 'software-development',
                'title' => 'مطوّر Laravel أول',
                'description' => 'نبحث عن مطور Laravel متمرس للعمل على منصتنا السحابية. ستكون مسؤولاً عن تصميم واجهات API وكتابة اختبارات ومراجعة كود الفريق.',
                'employment_type' => 'full_time',
                'workplace_type' => 'remote',
                'location' => 'دبي، الإمارات',
                'salary_min' => 12000,
                'salary_max' => 18000,
                'status' => 'open',
                'published_days_ago' => 10,
                'expires_in_days' => 20,
                'skills' => [
                    'Laravel' => true,
                    'PHP' => true,
                    'MySQL' => true,
                    'Docker' => false,
                    'Git' => false,
                ],
            ],
            [
                'employer' => 'employer@example.com',
                'category' => 'software-development',
                'title' => 'مطوّر واجهات أمامية (React)',
                'description' => 'انضم إلى فريق الواجهات لبناء تجارب استخدام سريعة وسهلة الوصول باستخدام React و TypeScript.',
                'employment_type' => 'full_time',
                'workplace_type' => 'hybrid',
                'location' => 'أبوظبي، الإمارات',
                'salary_min' => 9000,
                'salary_max' => 14000,
                'status' => 'open',
                'published_days_ago' => 5,
                'expires_in_days' => 25,
                'skills' => [
                    'JavaScript' => true,
                    'React' => true,
                    'TypeScript' => false,
                    'Tailwind CSS' => false,
                ],
            ],
            [
                'employer' => 'employer2@example.com',
                'category' => 'data-analytics',
                'title' => 'محلل بيانات',
                'description' => 'مسؤول عن تحليل بيانات المنتج وإعداد التقارير ولوحات المعلومات لدعم قرارات الفريق.',
                'employment_type' => 'full_time',
                'workplace_type' => 'on_site',
                'location' => 'الرياض، السعودية',
                'salary_min' => 8000,
                'salary_max' => 12000,
                'status' => 'open',
                'published_days_ago' => 7,
                'expires_in_days' => 30,
                'skills' => [
                    'تحليل البيانات' => true,
                    'Python' => true,
                    'PostgreSQL' => false,
                ],
            ],
            [
                'employer' => 'employer2@example.com',
                'category' => 'ui-ux-design',
                'title' => 'مصمم تجربة وواجهة المستخدم',
                'description' => 'تصميم واجهات وتجارب استخدام لتطبيقات الويب والجوال، مع العمل عن قرب مع فرق المنتج والهندسة.',
                'employment_type' => 'part_time',
                'workplace_type' => 'remote',
                'location' => 'عمّان، الأردن',
                'salary_min' => 5000,
                'salary_max' => 8000,
                'status' => 'open',
                'published_days_ago' => 3,
                'expires_in_days' => 40,
                'skills' => [
                    'تصميم واجهات وتجارب المستخدم' => true,
                    'اللغة الإنجليزية' => true,
                ],
            ],
            [
                'employer' => 'employer@example.com',
                'category' => 'digital-marketing',
                'title' => 'أخصائي تسويق رقمي',
                'description' => 'إدارة الحملات الإعلانية على المنصات الرقمية وتحسين معدلات التحويل.',
                'employment_type' => 'full_time',
                'workplace_type' => 'on_site',
                'location' => 'الدوحة، قطر',
                'salary_min' => 6000,
                'salary_max' => 9000,
                'status' => 'closed',
                'published_days_ago' => 60,
                'expires_in_days' => null,
                'skills' => [
                    'التسويق الرقمي' => true,
                    'اللغة الإنجليزية' => true,
                ],
            ],
            [
                'employer' => 'employer2@example.com',
                'category' => 'software-development',
                'title' => 'مهندس DevOps',
                'description' => 'بناء وصيانة خطوط CI/CD والبنية التحتية السحابية ومراقبة الأنظمة.',
                'employment_type' => 'full_time',
                'workplace_type' => 'remote',
                'location' => 'عن بُعد',
                'salary_min' => 13000,
                'salary_max' => 19000,
                'status' => 'open',
                'published_days_ago' => 2,
                'expires_in_days' => 45,
                'skills' => [
                    'Docker' => true,
                    'Git' => true,
                    'Python' => false,
                ],
            ],
            [
                'employer' => 'employer@example.com',
                'category' => 'customer-support',
                'title' => 'أخصائي دعم العملاء',
                'description' => 'تقديم الدعم للعملاء عبر المحادثات والبريد الإلكتروني وحل المشكلات التقنية البسيطة.',
                'employment_type' => 'part_time',
                'workplace_type' => 'on_site',
                'location' => 'دبي، الإمارات',
                'salary_min' => 3500,
                'salary_max' => 5000,
                'status' => 'draft',
                'published_days_ago' => null,
                'expires_in_days' => 30,
                'skills' => [
                    'دعم العملاء' => true,
                    'اللغة الإنجليزية' => true,
                    'الكتابة بالعربية' => false,
                ],
            ],
            [
                'employer' => 'employer2@example.com',
                'category' => 'sales',
                'title' => 'مسؤول مبيعات',
                'description' => 'تطوير علاقات العملاء وإغلاق صفقات جديدة في السوق الخليجي.',
                'employment_type' => 'full_time',
                'workplace_type' => 'on_site',
                'location' => 'الرياض، السعودية',
                'salary_min' => 7000,
                'salary_max' => 11000,
                'status' => 'filled',
                'published_days_ago' => 45,
                'expires_in_days' => null,
                'skills' => [
                    'المبيعات' => true,
                    'إدارة المشاريع' => false,
                ],
            ],
            [
                'employer' => 'employer@example.com',
                'category' => 'software-development',
                'title' => 'مطوّر خلفية (PHP)',
                'description' => 'عقد مؤقت لبناء تكاملات REST API وتحسين أداء الاستعلامات.',
                'employment_type' => 'contract',
                'workplace_type' => 'remote',
                'location' => 'عن بُعد',
                'salary_min' => 8000,
                'salary_max' => 12000,
                'status' => 'open',
                'published_days_ago' => 90,
                'expires_in_days' => -2,
                'skills' => [
                    'PHP' => true,
                    'Laravel' => true,
                    'REST APIs' => true,
                ],
            ],
            [
                'employer' => 'employer2@example.com',
                'category' => 'content-writing',
                'title' => 'كاتب تقني',
                'description' => 'كتابة أدلة الاستخدام والتوثيق التقني بالعربية والإنجليزية.',
                'employment_type' => 'contract',
                'workplace_type' => 'remote',
                'location' => 'عن بُعد',
                'salary_min' => 4000,
                'salary_max' => 7000,
                'status' => 'open',
                'published_days_ago' => 1,
                'expires_in_days' => 60,
                'skills' => [
                    'الكتابة التقنية' => true,
                    'اللغة الإنجليزية' => true,
                    'الكتابة بالعربية' => false,
                ],
            ],
        ];

        foreach ($listings as $data) {
            $employer = User::query()->where('email', $data['employer'])->firstOrFail();
            $category = Category::query()->where('slug', $data['category'])->first();

            // Identity is pinned to the description so translated titles update
            // existing demo rows in place instead of creating duplicates.
            $listing = JobListing::updateOrCreate(
                [
                    'posted_by' => $employer->id,
                    'description' => $data['description'],
                ],
                [
                    'title' => $data['title'],
                    'category_id' => $category?->id,
                    'employment_type' => $data['employment_type'],
                    'workplace_type' => $data['workplace_type'],
                    'location' => $data['location'],
                    'salary_min' => $data['salary_min'],
                    'salary_max' => $data['salary_max'],
                    'salary_currency' => 'USD',
                    'status' => $data['status'],
                    'published_at' => $data['published_days_ago'] === null
                        ? null
                        : now()->subDays($data['published_days_ago']),
                    'expires_at' => $data['expires_in_days'] === null
                        ? null
                        : now()->addDays($data['expires_in_days']),
                ],
            );

            $skillIds = Skill::query()
                ->whereIn('name', array_keys($data['skills']))
                ->pluck('id', 'name');

            $pivot = [];

            foreach ($data['skills'] as $name => $isRequired) {
                if ($skillIds->has($name)) {
                    $pivot[$skillIds[$name]] = ['is_required' => $isRequired];
                }
            }

            if ($pivot !== []) {
                $listing->skills()->syncWithoutDetaching($pivot);
            }
        }
    }
}
