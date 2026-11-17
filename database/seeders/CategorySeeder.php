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
                'name' => 'Technology',
                'children' => [
                    'software-development' => 'Software Development',
                    'data-analytics' => 'Data & Analytics',
                    'it-support' => 'IT Support',
                ],
            ],
            'design' => [
                'name' => 'Design',
                'children' => [
                    'ui-ux-design' => 'UI/UX Design',
                    'graphic-design' => 'Graphic Design',
                ],
            ],
            'marketing' => [
                'name' => 'Marketing',
                'children' => [
                    'digital-marketing' => 'Digital Marketing',
                    'content-writing' => 'Content Writing',
                ],
            ],
            'sales' => [
                'name' => 'Sales',
                'children' => [],
            ],
            'customer-support' => [
                'name' => 'Customer Support',
                'children' => [],
            ],
            'finance' => [
                'name' => 'Finance',
                'children' => [],
            ],
        ];

        foreach ($categories as $slug => $parent) {
            $parentCategory = Category::firstOrCreate(
                ['slug' => $slug],
                ['name' => $parent['name']],
            );

            foreach ($parent['children'] as $childSlug => $childName) {
                Category::firstOrCreate(
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
