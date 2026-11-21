<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_applications_receive_a_status_timeline(): void
    {
        $this->seed();

        $applications = Application::query()->with('statusHistories')->get();

        $this->assertNotEmpty($applications);

        foreach ($applications as $application) {
            $this->assertNotEmpty(
                $application->statusHistories,
                "الطلب #{$application->id} لا يملك سجل حالات بعد التهيئة.",
            );

            $this->assertSame(
                $application->status,
                $application->statusHistories->first()->to_status,
                "آخر حالة في سجل الطلب #{$application->id} لا تطابق حالته الحالية.",
            );
        }
    }

    public function test_the_interview_application_has_the_expected_status_path(): void
    {
        $this->seed();

        $application = Application::query()
            ->whereHas('user', fn (Builder $query) => $query->where('email', 'seeker@example.com'))
            ->whereHas('jobListing', fn (Builder $query) => $query->where('title', 'Senior Laravel Developer'))
            ->firstOrFail();

        $this->assertSame(
            [Application::STATUS_APPLIED, Application::STATUS_REVIEWED, Application::STATUS_INTERVIEW],
            ApplicationStatusHistory::query()
                ->where('application_id', $application->id)
                ->orderBy('id')
                ->pluck('to_status')
                ->all(),
        );
    }

    public function test_reseeding_does_not_duplicate_status_history(): void
    {
        $this->seed();
        $historyCount = ApplicationStatusHistory::query()->count();

        $this->seed();

        $this->assertSame($historyCount, ApplicationStatusHistory::query()->count());
    }
}
