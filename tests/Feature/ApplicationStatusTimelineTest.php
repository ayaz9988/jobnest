<?php

namespace Tests\Feature;

use App\Actions\Applications\TransitionApplication;
use App\Actions\Applications\WithdrawApplication;
use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationStatusTimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_applying_records_the_initial_status(): void
    {
        $application = $this->applicationFor(User::factory()->create());

        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->id,
            'from_status' => null,
            'to_status' => Application::STATUS_APPLIED,
        ]);
    }

    public function test_a_status_transition_records_the_previous_and_new_statuses(): void
    {
        $application = $this->applicationFor(User::factory()->create());

        app(TransitionApplication::class)->handle($application, Application::STATUS_REVIEWED);

        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->id,
            'from_status' => Application::STATUS_APPLIED,
            'to_status' => Application::STATUS_REVIEWED,
        ]);
    }

    public function test_withdrawing_records_the_status_change(): void
    {
        $application = $this->applicationFor(User::factory()->create());

        app(WithdrawApplication::class)->handle($application);

        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->id,
            'from_status' => Application::STATUS_APPLIED,
            'to_status' => Application::STATUS_WITHDRAWN,
        ]);
    }

    public function test_the_seeker_sees_the_status_timeline_on_the_application_page(): void
    {
        $user = User::factory()->create();
        $application = $this->applicationFor($user);

        $transitions = app(TransitionApplication::class);
        $transitions->handle($application, Application::STATUS_REVIEWED);
        $transitions->handle($application->refresh(), Application::STATUS_INTERVIEW);

        $this->actingAs($user)
            ->get(route('applications.show', $application))
            ->assertOk()
            ->assertSee('مراحل الطلب')
            ->assertSee('تم التقديم')
            ->assertSee('قيد المراجعة')
            ->assertSee('مقابلة');
    }

    public function test_another_seeker_cannot_see_the_timeline(): void
    {
        $owner = User::factory()->create();
        $application = $this->applicationFor($owner);

        $this->actingAs(User::factory()->create())
            ->get(route('applications.show', $application))
            ->assertForbidden();
    }

    private function applicationFor(User $user): Application
    {
        return Application::query()->create([
            'user_id' => $user->id,
            'job_listing_id' => JobListing::factory()->create()->id,
            'status' => Application::STATUS_APPLIED,
            'applied_at' => now(),
        ]);
    }
}
