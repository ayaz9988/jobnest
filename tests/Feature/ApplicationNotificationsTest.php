<?php

namespace Tests\Feature;

use App\Actions\Applications\WithdrawApplication;
use App\Models\Application;
use App\Models\JobListing;
use App\Models\Resume;
use App\Models\User;
use App\Notifications\ApplicationStatusChanged;
use App\Notifications\ApplicationSubmitted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ApplicationNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_applying_notifies_the_seeker_on_the_database_and_mail_channels(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        Resume::factory()->primary()->create(['user_id' => $user->id]);
        $job = JobListing::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::jobs.show', ['jobListing' => $job])
            ->call('apply')
            ->assertHasNoErrors();

        Notification::assertSentTo(
            $user,
            ApplicationSubmitted::class,
            fn (ApplicationSubmitted $notification, array $channels): bool => in_array('database', $channels, true)
                && in_array('mail', $channels, true),
        );
    }

    public function test_reapplying_after_withdrawal_sends_the_submitted_notification_again(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        Resume::factory()->primary()->create(['user_id' => $user->id]);
        $job = JobListing::factory()->create();

        Application::query()->create([
            'user_id' => $user->id,
            'job_listing_id' => $job->id,
            'status' => Application::STATUS_WITHDRAWN,
            'applied_at' => now()->subWeek(),
        ]);

        $this->actingAs($user);

        Livewire::test('pages::jobs.show', ['jobListing' => $job])
            ->call('apply')
            ->assertHasNoErrors();

        Notification::assertSentToTimes($user, ApplicationSubmitted::class, 1);
    }

    public function test_a_status_change_notifies_the_seeker(): void
    {
        $user = User::factory()->create();
        $application = $this->makeApplication($user);

        Notification::fake();

        $application->update(['status' => Application::STATUS_REVIEWED]);

        Notification::assertSentTo($user, ApplicationStatusChanged::class);
    }

    public function test_withdrawing_does_not_send_a_status_change_notification(): void
    {
        $user = User::factory()->create();
        $application = $this->makeApplication($user);

        Notification::fake();

        app(WithdrawApplication::class)->handle($application);

        Notification::assertNotSentTo($user, ApplicationStatusChanged::class);
    }

    public function test_updates_that_do_not_change_the_status_do_not_notify(): void
    {
        $user = User::factory()->create();
        $application = $this->makeApplication($user);

        Notification::fake();

        $application->update(['cover_letter' => 'رسالة محدثة.']);

        Notification::assertNotSentTo($user, ApplicationStatusChanged::class);
    }

    public function test_guests_are_redirected_from_the_notifications_page(): void
    {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
    }

    public function test_the_notifications_page_only_lists_the_current_users_notifications(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $ownJob = JobListing::factory()->create(['title' => 'وظيفة إشعاري']);
        $otherJob = JobListing::factory()->create(['title' => 'وظيفة شخص آخر']);

        $ownApplication = $this->makeApplication($user, $ownJob, Application::STATUS_WITHDRAWN);
        $otherApplication = $this->makeApplication($other, $otherJob, Application::STATUS_WITHDRAWN);

        $user->notify(new ApplicationSubmitted($ownApplication));
        $other->notify(new ApplicationSubmitted($otherApplication));

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee($ownJob->title)
            ->assertDontSee($otherJob->title);
    }

    public function test_all_notifications_can_be_marked_as_read(): void
    {
        $user = User::factory()->create();
        $application = $this->makeApplication($user, status: Application::STATUS_WITHDRAWN);

        $user->notify(new ApplicationSubmitted($application));
        $user->notify(new ApplicationSubmitted($application));

        $this->actingAs($user);

        Livewire::test('pages::notifications.index')
            ->call('markAllAsRead');

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_opening_a_notification_marks_it_read_and_redirects_to_the_application(): void
    {
        $user = User::factory()->create();
        $application = $this->makeApplication($user, status: Application::STATUS_WITHDRAWN);

        $user->notify(new ApplicationSubmitted($application));

        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user);

        Livewire::test('pages::notifications.index')
            ->call('open', $notification->id)
            ->assertRedirect(route('applications.show', $application));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_a_notification_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $application = $this->makeApplication($user, status: Application::STATUS_WITHDRAWN);

        $user->notify(new ApplicationSubmitted($application));

        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user);

        Livewire::test('pages::notifications.index')
            ->call('delete', $notification->id);

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    private function makeApplication(
        User $user,
        ?JobListing $job = null,
        string $status = Application::STATUS_APPLIED,
    ): Application {
        return Application::query()->create([
            'user_id' => $user->id,
            'job_listing_id' => ($job ?? JobListing::factory()->create())->id,
            'status' => $status,
            'applied_at' => now(),
        ]);
    }
}
