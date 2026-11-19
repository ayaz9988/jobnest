<?php

namespace App\Support;

use App\Models\User;

/**
 * Calculates how complete a seeker's profile is and whether the minimum
 * requirements for applying to a job have been met.
 */
final class SeekerProfileCompleteness
{
    private function __construct(
        private readonly bool $hasProfile,
        private readonly bool $hasLocation,
        private readonly bool $hasSkills,
        private readonly bool $hasResume,
        private readonly bool $hasExperience,
        private readonly bool $hasEducation,
    ) {}

    public static function for(User $user): self
    {
        return new self(
            filled($user->profile),
            filled($user->location),
            $user->skills()->exists(),
            $user->resumes()->exists(),
            $user->workExperiences()->exists(),
            $user->educations()->exists(),
        );
    }

    /**
     * @return array<string, array{label: string, complete: bool, required: bool}>
     */
    public function items(): array
    {
        return [
            'profile' => [
                'label' => 'نبذة شخصية',
                'complete' => $this->hasProfile,
                'required' => true,
            ],
            'location' => [
                'label' => 'الموقع',
                'complete' => $this->hasLocation,
                'required' => true,
            ],
            'skills' => [
                'label' => 'مهارة واحدة على الأقل',
                'complete' => $this->hasSkills,
                'required' => true,
            ],
            'resume' => [
                'label' => 'سيرة ذاتية',
                'complete' => $this->hasResume,
                'required' => true,
            ],
            'experience' => [
                'label' => 'خبرة عملية',
                'complete' => $this->hasExperience,
                'required' => false,
            ],
            'education' => [
                'label' => 'مؤهل تعليمي',
                'complete' => $this->hasEducation,
                'required' => false,
            ],
        ];
    }

    public function percentage(): int
    {
        $items = $this->items();

        $completed = count(array_filter(
            $items,
            fn (array $item): bool => $item['complete'],
        ));

        return (int) round($completed / count($items) * 100);
    }

    public function isComplete(): bool
    {
        return $this->percentage() === 100;
    }

    /**
     * Whether the seeker finished the minimum requirements needed to apply.
     */
    public function canApply(): bool
    {
        return $this->missingForApplying() === [];
    }

    /**
     * @return list<string>
     */
    public function missingForApplying(): array
    {
        $missing = [];

        foreach ($this->items() as $item) {
            if ($item['required'] && ! $item['complete']) {
                $missing[] = $item['label'];
            }
        }

        return $missing;
    }
}
