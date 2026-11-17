<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\JobListing;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JobApplicationTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $application = Application::query()->create([
            'user_id' => User::factory()->create()->id,
            'job_listing_id' => JobListing::factory()->create()->id,
            'status' => Application::STATUS_APPLIED,
            'applied_at' => now(),
        ]);

        $this->get(route('applications.index'))->assertRedirect(route('login'));
        $this->get(route('applications.show', $application))->assertRedirect(route('login'));
    }

    public function test_employers_cannot_access_the_applications_inbox(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $application = Application::query()->create([
            'user_id' => User::factory()->create()->id,
            'job_listing_id' => JobListing::factory()->create()->id,
            'status' => Application::STATUS_APPLIED,
            'applied_at' => now(),
        ]);

        $this->actingAs($employer)
            ->get(route('applications.index'))
            ->assertForbidden();

        $this->actingAs($employer)
            ->get(route('applications.show', $application))
            ->assertForbidden();
    }

    public function test_employers_cannot_access_the_saved_jobs_page(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);

        $this->actingAs($employer)
            ->get(route('jobs.saved'))
            ->assertForbidden();
    }

    public function test_the_inbox_only_lists_the_current_users_applications(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $ownJob = JobListing::factory()->create(['title' => 'وظيفة خاصة بي']);
        $otherJob = JobListing::factory()->create(['title' => 'وظيفة مستخدم آخر']);

        Application::query()->create([
            'user_id' => $user->id,
            'job_listing_id' => $ownJob->id,
            'status' => Application::STATUS_APPLIED,
            'applied_at' => now(),
        ]);

        Application::query()->create([
            'user_id' => $other->id,
            'job_listing_id' => $otherJob->id,
            'status' => Application::STATUS_APPLIED,
            'applied_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('applications.index'))
            ->assertOk()
            ->assertSee($ownJob->title)
            ->assertDontSee($otherJob->title);
    }

    public function test_a_user_cannot_view_another_users_application(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $application = Application::query()->create([
            'user_id' => $owner->id,
            'job_listing_id' => JobListing::factory()->create()->id,
            'status' => Application::STATUS_APPLIED,
            'applied_at' => now(),
        ]);

        $this->actingAs($intruder)
            ->get(route('applications.show', $application))
            ->assertForbidden();
    }

    public function test_the_inbox_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();

        $appliedJob = JobListing::factory()->create(['title' => 'وظيفة قيد التقديم']);
        $rejectedJob = JobListing::factory()->create(['title' => 'وظيفة مرفوضة']);

        Application::query()->create([
            'user_id' => $user->id,
            'job_listing_id' => $appliedJob->id,
            'status' => Application::STATUS_APPLIED,
            'applied_at' => now(),
        ]);

        Application::query()->create([
            'user_id' => $user->id,
            'job_listing_id' => $rejectedJob->id,
            'status' => Application::STATUS_REJECTED,
            'applied_at' => now(),
        ]);

        $this->actingAs($user);

        Livewire::test('pages::applications.index')
            ->set('status', Application::STATUS_REJECTED)
            ->assertSee($rejectedJob->title)
            ->assertDontSee($appliedJob->title);
    }

    public function test_a_seeker_can_withdraw_an_active_application(): void
    {
        $user = User::factory()->create();

        $application = Application::query()->create([
            'user_id' => $user->id,
            'job_listing_id' => JobListing::factory()->create()->id,
            'status' => Application::STATUS_APPLIED,
            'applied_at' => now(),
        ]);

        $this->actingAs($user);

        Livewire::test('pages::applications.index')
            ->call('withdraw', $application->id);

        $this->assertSame(Application::STATUS_WITHDRAWN, $application->fresh()->status);
    }

    public function test_a_hired_application_cannot_be_withdrawn(): void
    {
        $user = User::factory()->create();

        $application = Application::query()->create([
            'user_id' => $user->id,
            'job_listing_id' => JobListing::factory()->create()->id,
            'status' => Application::STATUS_HIRED,
            'applied_at' => now(),
        ]);

        $this->actingAs($user);

        Livewire::test('pages::applications.index')
            ->call('withdraw', $application->id);

        $this->assertSame(Application::STATUS_HIRED, $application->fresh()->status);
    }

    public function test_a_rejected_application_cannot_be_withdrawn(): void
    {
        $user = User::factory()->create();

        $application = Application::query()->create([
            'user_id' => $user->id,
            'job_listing_id' => JobListing::factory()->create()->id,
            'status' => Application::STATUS_REJECTED,
            'applied_at' => now(),
        ]);

        $this->actingAs($user);

        Livewire::test('pages::applications.index')
            ->call('withdraw', $application->id);

        $this->assertSame(Application::STATUS_REJECTED, $application->fresh()->status);
    }

    public function test_a_user_cannot_withdraw_another_users_application(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $application = Application::query()->create([
            'user_id' => $owner->id,
            'job_listing_id' => JobListing::factory()->create()->id,
            'status' => Application::STATUS_APPLIED,
            'applied_at' => now(),
        ]);

        $this->actingAs($intruder);

        Livewire::test('pages::applications.index')
            ->call('withdraw', $application->id)
            ->assertNotFound();
    }

    public function test_the_detail_page_shows_the_application_snapshot(): void
    {
        $user = User::factory()->create();
        $resume = Resume::factory()->primary()->create(['user_id' => $user->id]);

        $job = JobListing::factory()->create([
            'title' => 'وظيفة تفاصيل الطلب',
        ]);

        $application = Application::query()->create([
            'user_id' => $user->id,
            'job_listing_id' => $job->id,
            'resume_id' => $resume->id,
            'status' => Application::STATUS_REVIEWED,
            'cover_letter' => 'أود الانضمام إلى فريقكم.',
            'applied_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('applications.show', $application))
            ->assertOk()
            ->assertSee($job->title)
            ->assertSee('قيد المراجعة')
            ->assertSee('أود الانضمام إلى فريقكم.')
            ->assertSee($resume->file_name);
    }

    public function test_a_seeker_can_withdraw_from_the_detail_page(): void
    {
        $user = User::factory()->create();

        $application = Application::query()->create([
            'user_id' => $user->id,
            'job_listing_id' => JobListing::factory()->create()->id,
            'status' => Application::STATUS_INTERVIEW,
            'applied_at' => now(),
        ]);

        $this->actingAs($user);

        Livewire::test('pages::applications.show', ['application' => $application])
            ->call('withdraw');

        $this->assertSame(Application::STATUS_WITHDRAWN, $application->fresh()->status);
    }

    public function test_a_withdrawn_application_can_be_reapplied_to_without_creating_a_new_row(): void
    {
        $user = User::factory()->create();
        $resume = Resume::factory()->primary()->create(['user_id' => $user->id]);
        $job = JobListing::factory()->create();

        $application = Application::query()->create([
            'user_id' => $user->id,
            'job_listing_id' => $job->id,
            'resume_id' => $resume->id,
            'status' => Application::STATUS_WITHDRAWN,
            'applied_at' => now()->subWeek(),
        ]);

        $this->actingAs($user);

        Livewire::test('pages::jobs.show', ['jobListing' => $job])
            ->set('coverLetter', 'أرغب بالعودة إلى التقديم.')
            ->call('apply')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('applications', 1);

        $application->refresh();

        $this->assertSame(Application::STATUS_APPLIED, $application->status);
        $this->assertSame('أرغب بالعودة إلى التقديم.', $application->cover_letter);
    }

    public function test_the_job_page_offers_the_apply_form_again_after_withdrawal(): void
    {
        $user = User::factory()->create();
        Resume::factory()->primary()->create(['user_id' => $user->id]);
        $job = JobListing::factory()->create();

        Application::query()->create([
            'user_id' => $user->id,
            'job_listing_id' => $job->id,
            'status' => Application::STATUS_WITHDRAWN,
            'applied_at' => now()->subWeek(),
        ]);

        $this->actingAs($user)
            ->get(route('jobs.show', $job))
            ->assertOk()
            ->assertSee('يمكنك التقديم مرة أخرى')
            ->assertSee('إرسال الطلب');
    }

    public function test_employers_cannot_apply_to_a_job(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $job = JobListing::factory()->create();

        $this->actingAs($employer);

        Livewire::test('pages::jobs.show', ['jobListing' => $job])
            ->call('apply')
            ->assertForbidden();
    }

    public function test_employers_cannot_save_a_job(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $job = JobListing::factory()->create();

        $this->actingAs($employer);

        Livewire::test('pages::jobs.index')
            ->call('toggleSave', $job->id)
            ->assertForbidden();
    }
}
