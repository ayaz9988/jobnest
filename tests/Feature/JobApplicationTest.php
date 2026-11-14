<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\JobListing;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JobApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_seeker_can_apply_with_their_primary_resume(): void
    {
        $user = User::factory()->create();
        $primary = Resume::factory()->primary()->create(['user_id' => $user->id]);
        $job = JobListing::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::jobs.show', ['jobListing' => $job])
            ->assertSet('resumeId', (string) $primary->id)
            ->set('coverLetter', 'أرغب بالانضمام إلى فريقكم.')
            ->call('apply')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('applications', [
            'user_id' => $user->id,
            'job_listing_id' => $job->id,
            'resume_id' => $primary->id,
            'status' => 'applied',
            'cover_letter' => 'أرغب بالانضمام إلى فريقكم.',
        ]);
    }

    public function test_the_primary_resume_is_selected_by_default_even_when_another_resume_is_newer(): void
    {
        $user = User::factory()->create();
        $primary = Resume::factory()->primary()->create(['user_id' => $user->id]);
        Resume::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test('pages::jobs.show', ['jobListing' => JobListing::factory()->create()])
            ->assertSet('resumeId', (string) $primary->id);
    }

    public function test_cover_letter_is_optional(): void
    {
        $user = User::factory()->create();
        Resume::factory()->primary()->create(['user_id' => $user->id]);
        $job = JobListing::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::jobs.show', ['jobListing' => $job])
            ->call('apply')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('applications', [
            'user_id' => $user->id,
            'job_listing_id' => $job->id,
            'cover_letter' => null,
        ]);
    }

    public function test_a_resume_is_required_to_apply(): void
    {
        $user = User::factory()->create();
        $job = JobListing::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::jobs.show', ['jobListing' => $job])
            ->call('apply')
            ->assertHasErrors(['resumeId']);

        $this->assertDatabaseCount('applications', 0);
    }

    public function test_a_resume_belonging_to_another_user_cannot_be_used(): void
    {
        $user = User::factory()->create();
        Resume::factory()->primary()->create(['user_id' => $user->id]);
        $foreignResume = Resume::factory()->create();
        $job = JobListing::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::jobs.show', ['jobListing' => $job])
            ->set('resumeId', (string) $foreignResume->id)
            ->call('apply')
            ->assertHasErrors(['resumeId']);

        $this->assertDatabaseCount('applications', 0);
    }

    public function test_duplicate_applications_are_blocked(): void
    {
        $user = User::factory()->create();
        Resume::factory()->primary()->create(['user_id' => $user->id]);
        $job = JobListing::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::jobs.show', ['jobListing' => $job])
            ->call('apply')
            ->assertHasNoErrors();

        Livewire::test('pages::jobs.show', ['jobListing' => $job])
            ->call('apply')
            ->assertHasErrors(['application']);

        $this->assertDatabaseCount('applications', 1);
    }

    public function test_the_cover_letter_must_not_exceed_the_limit(): void
    {
        $user = User::factory()->create();
        Resume::factory()->primary()->create(['user_id' => $user->id]);
        $job = JobListing::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::jobs.show', ['jobListing' => $job])
            ->set('coverLetter', str_repeat('ا', 2001))
            ->call('apply')
            ->assertHasErrors(['coverLetter']);

        $this->assertDatabaseCount('applications', 0);
    }

    public function test_a_closed_job_listing_does_not_accept_applications(): void
    {
        $user = User::factory()->create();
        Resume::factory()->primary()->create(['user_id' => $user->id]);
        $job = JobListing::factory()->closed()->create();

        $this->actingAs($user);

        Livewire::test('pages::jobs.show', ['jobListing' => $job])
            ->call('apply')
            ->assertHasErrors(['application']);

        $this->assertDatabaseCount('applications', 0);
    }

    public function test_an_expired_job_listing_does_not_accept_applications(): void
    {
        $user = User::factory()->create();
        Resume::factory()->primary()->create(['user_id' => $user->id]);
        $job = JobListing::factory()->expired()->create();

        $this->actingAs($user);

        Livewire::test('pages::jobs.show', ['jobListing' => $job])
            ->call('apply')
            ->assertHasErrors(['application']);

        $this->assertDatabaseCount('applications', 0);
    }

    public function test_a_seeker_who_already_applied_sees_their_application_status(): void
    {
        $user = User::factory()->create();
        $resume = Resume::factory()->primary()->create(['user_id' => $user->id]);
        $job = JobListing::factory()->create();

        Application::query()->create([
            'user_id' => $user->id,
            'job_listing_id' => $job->id,
            'resume_id' => $resume->id,
            'status' => 'reviewed',
            'applied_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('jobs.show', $job))
            ->assertOk()
            ->assertSee('لقد تقدمت لهذه الوظيفة.')
            ->assertSee('قيد المراجعة')
            ->assertDontSee('إرسال الطلب');
    }

    public function test_guests_cannot_view_the_application_form(): void
    {
        $job = JobListing::factory()->create();

        $this->get(route('jobs.show', $job))->assertRedirect(route('login'));
    }
}
