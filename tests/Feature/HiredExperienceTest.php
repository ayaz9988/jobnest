<?php

namespace Tests\Feature;

use App\Actions\Applications\TransitionApplication;
use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;
use App\Notifications\ApplicationHired;
use App\Notifications\ApplicationStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class HiredExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_marking_an_application_hired_sends_the_hired_notification(): void
    {
        $user = User::factory()->create();
        $application = $this->applicationFor($user);

        Notification::fake();

        $transitions = app(TransitionApplication::class);
        $transitions->handle($application, Application::STATUS_REVIEWED);
        $transitions->handle($application->refresh(), Application::STATUS_INTERVIEW);
        $transitions->handle($application->refresh(), Application::STATUS_HIRED);

        Notification::assertSentTo($user, ApplicationHired::class);
    }

    public function test_other_status_changes_keep_using_the_generic_notification(): void
    {
        $user = User::factory()->create();
        $application = $this->applicationFor($user);

        Notification::fake();

        app(TransitionApplication::class)->handle($application, Application::STATUS_REVIEWED);

        Notification::assertSentTo($user, ApplicationStatusChanged::class);
        Notification::assertNotSentTo($user, ApplicationHired::class);
    }

    public function test_the_seeker_sees_the_hired_banner(): void
    {
        $user = User::factory()->create();
        $application = $this->applicationFor($user);

        $application->update(['status' => Application::STATUS_HIRED]);

        $this->actingAs($user)
            ->get(route('applications.show', $application))
            ->assertOk()
            ->assertSee('تهانينا! تم توظيفك')
            ->assertSee($application->jobListing->title);
    }

    public function test_the_banner_is_not_shown_before_being_hired(): void
    {
        $user = User::factory()->create();
        $application = $this->applicationFor($user);

        $this->actingAs($user)
            ->get(route('applications.show', $application))
            ->assertOk()
            ->assertDontSee('تهانينا! تم توظيفك');
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
