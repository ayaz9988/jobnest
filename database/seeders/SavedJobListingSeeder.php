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
                'Senior Laravel Developer',
                'Frontend Developer (React)',
                'Data Analyst',
            ],
            'sara@example.com' => [
                'UI/UX Designer',
                'Technical Writer',
            ],
            'omar@example.com' => [
                'Data Analyst',
                'DevOps Engineer',
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
