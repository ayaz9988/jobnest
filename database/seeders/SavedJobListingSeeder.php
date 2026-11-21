<?php

namespace Database\Seeders;

use App\Models\JobListing;
use App\Models\User;
use Illuminate\Database\Seeder;

class SavedJobListingSeeder extends Seeder
{
    public function run(): void
    {
        $savedJobs = [
            'seeker@example.com' => [
                'مطوّر Laravel أول',
                'مطوّر واجهات أمامية (React)',
                'محلل بيانات',
            ],
            'sara@example.com' => [
                'مصمم تجربة وواجهة المستخدم',
                'كاتب تقني',
            ],
            'omar@example.com' => [
                'محلل بيانات',
                'مهندس DevOps',
            ],
        ];

        foreach ($savedJobs as $email => $titles) {
            $user = User::query()->where('email', $email)->firstOrFail();

            $jobIds = JobListing::query()
                ->whereIn('title', $titles)
                ->pluck('id');

            if ($jobIds->isNotEmpty()) {
                $user->savedJobListings()->syncWithoutDetaching($jobIds);
            }
        }
    }
}
