<?php

namespace Tests\Concerns;

use App\Models\Skill;
use App\Models\User;
use App\Models\WorkExperience;

trait CompletesSeekerProfiles
{
    /**
     * Fill the minimum profile requirements for applying to a job.
     */
    protected function completeSeekerProfile(User $user): void
    {
        $user->update([
            'profile' => 'باحث عن عمل في تطوير البرمجيات.',
            'location' => 'الرياض، السعودية',
        ]);

        $user->skills()->syncWithoutDetaching([Skill::factory()->create()->id]);

        WorkExperience::factory()->create(['user_id' => $user->id]);
    }
}
