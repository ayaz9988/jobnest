<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'technology' => [
                'name' => 'التقنية',
                'children' => [
                    'software-development' => 'تطوير البرمجيات',
                    'data-analytics' => 'البيانات والتحليلات',
                    'it-support' => 'الدعم التقني',
                ],
            ],
            'design' => [
                'name' => 'التصميم',
                'children' => [
                    'ui-ux-design' => 'تصميم واجهات وتجارب المستخدم',
                    'graphic-design' => 'التصميم الجرافيكي',
                ],
            ],
            'marketing' => [
                'name' => 'التسويق',
                'children' => [
                    'digital-marketing' => 'التسويق الرقمي',
                    'content-writing' => 'كتابة المحتوى',
                ],
            ],
            'sales' => [
                'name' => 'المبيعات',
                'children' => [],
            ],
            'customer-support' => [
                'name' => 'دعم العملاء',
                'children' => [],
            ],
            'finance' => [
                'name' => 'المالية',
                'children' => [],
            ],
        ];

        foreach ($categories as $slug => $parent) {
            $parentCategory = Category::updateOrCreate(
                ['slug' => $slug],
                ['name' => $parent['name']],
            );

            foreach ($parent['children'] as $childSlug => $childName) {
                Category::updateOrCreate(
                    ['slug' => $childSlug],
                    [
                        'name' => $childName,
                        'parent_id' => $parentCategory->id,
                    ],
                );
            }
        }
    }
}
