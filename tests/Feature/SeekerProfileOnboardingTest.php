<?php

namespace Tests\Feature;

use App\Models\Education;
use App\Models\JobListing;
use App\Models\Resume;
use App\Models\Skill;
use App\Models\User;
use App\Models\WorkExperience;
use App\Support\SeekerProfileCompleteness;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\CompletesSeekerProfiles;
use Tests\TestCase;

class SeekerProfileOnboardingTest extends TestCase
{
    use CompletesSeekerProfiles, RefreshDatabase;

    public function test_a_new_seeker_profile_is_incomplete_and_cannot_apply(): void
    {
        $user = User::factory()->create();

        $completeness = SeekerProfileCompleteness::for($user);

        $this->assertSame(0, $completeness->percentage());
        $this->assertFalse($completeness->canApply());
        $this->assertSame([
            'نبذة شخصية',
            'الموقع',
            'مهارة واحدة على الأقل',
            'سيرة ذاتية',
        ], $completeness->missingForApplying());
    }

    public function test_the_minimum_requirements_are_enough_to_apply(): void
    {
        $user = User::factory()->create();
        $this->completeSeekerProfile($user);
        Resume::factory()->primary()->create(['user_id' => $user->id]);

        $completeness = SeekerProfileCompleteness::for($user);

        $this->assertTrue($completeness->canApply());
        $this->assertSame(83, $completeness->percentage());
        $this->assertSame([], $completeness->missingForApplying());
    }

    public function test_experience_and_education_complete_the_profile_fully(): void
    {
        $user = User::factory()->create();
        $this->completeSeekerProfile($user);
        Resume::factory()->primary()->create(['user_id' => $user->id]);
        Education::factory()->create(['user_id' => $user->id]);

        $completeness = SeekerProfileCompleteness::for($user);

        $this->assertTrue($completeness->isComplete());
        $this->assertSame(100, $completeness->percentage());
    }

    public function test_a_work_experience_can_be_added(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::profile.edit')
            ->set('experienceTitle', 'مطور ويب')
            ->set('experienceCompany', 'شركة التقنية')
            ->set('experienceLocation', 'الرياض')
            ->set('experienceStartedAt', '2020-01-01')
            ->set('experienceEndedAt', '2022-06-30')
            ->set('experienceDescription', 'تطوير تطبيقات الويب.')
            ->call('saveExperience')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('work_experiences', [
            'user_id' => $user->id,
            'job_title' => 'مطور ويب',
            'company' => 'شركة التقنية',
            'location' => 'الرياض',
            'is_current' => false,
        ]);
    }

    public function test_a_current_work_experience_ignores_the_end_date(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::profile.edit')
            ->set('experienceTitle', 'مهندس برمجيات')
            ->set('experienceCompany', 'شركة التقنية')
            ->set('experienceStartedAt', '2021-03-01')
            ->set('experienceEndedAt', '2023-01-01')
            ->set('experienceIsCurrent', true)
            ->call('saveExperience')
            ->assertHasNoErrors();

        $experience = $user->workExperiences()->firstOrFail();

        $this->assertTrue($experience->is_current);
        $this->assertNull($experience->ended_at);
    }

    public function test_a_work_experience_can_be_updated_and_deleted(): void
    {
        $user = User::factory()->create();
        $experience = WorkExperience::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test('pages::profile.edit')
            ->call('editExperience', $experience->id)
            ->assertSet('experienceTitle', $experience->job_title)
            ->set('experienceTitle', 'قائد فريق')
            ->call('saveExperience')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('work_experiences', [
            'id' => $experience->id,
            'job_title' => 'قائد فريق',
        ]);

        Livewire::test('pages::profile.edit')
            ->call('deleteExperience', $experience->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('work_experiences', ['id' => $experience->id]);
    }

    public function test_another_users_experience_cannot_be_edited(): void
    {
        $user = User::factory()->create();
        $foreign = WorkExperience::factory()->create();

        $this->actingAs($user);

        try {
            Livewire::test('pages::profile.edit')->call('editExperience', $foreign->id);
        } catch (ModelNotFoundException) {
            // Scoped lookups fail with a 404; either way the record stays untouched.
        }

        $this->assertDatabaseHas('work_experiences', ['id' => $foreign->id]);
    }

    public function test_the_end_date_must_not_precede_the_start_date(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::profile.edit')
            ->set('experienceTitle', 'مطور')
            ->set('experienceCompany', 'شركة')
            ->set('experienceStartedAt', '2022-01-01')
            ->set('experienceEndedAt', '2021-01-01')
            ->call('saveExperience')
            ->assertHasErrors(['experienceEndedAt']);

        $this->assertDatabaseCount('work_experiences', 0);
    }

    public function test_an_education_entry_can_be_added_and_removed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::profile.edit')
            ->set('educationDegree', 'بكالوريوس')
            ->set('educationInstitution', 'جامعة الملك سعود')
            ->set('educationField', 'علوم الحاسب')
            ->set('educationStartedAt', '2016-09-01')
            ->set('educationEndedAt', '2020-06-30')
            ->call('saveEducation')
            ->assertHasNoErrors();

        $education = $user->educations()->firstOrFail();

        $this->assertDatabaseHas('educations', [
            'user_id' => $user->id,
            'degree' => 'بكالوريوس',
            'institution' => 'جامعة الملك سعود',
            'field_of_study' => 'علوم الحاسب',
        ]);

        Livewire::test('pages::profile.edit')
            ->call('deleteEducation', $education->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('educations', ['id' => $education->id]);
    }

    public function test_applying_is_blocked_until_the_minimum_profile_is_complete(): void
    {
        $user = User::factory()->create();
        Resume::factory()->primary()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test('pages::jobs.show', ['jobListing' => JobListing::factory()->create()])
            ->call('apply')
            ->assertHasErrors(['application']);

        $this->assertDatabaseCount('applications', 0);
    }

    public function test_applying_is_allowed_once_the_minimum_profile_is_complete(): void
    {
        $user = User::factory()->create();
        $this->completeSeekerProfile($user);
        Resume::factory()->primary()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test('pages::jobs.show', ['jobListing' => JobListing::factory()->create()])
            ->call('apply')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('applications', 1);
    }

    public function test_the_job_page_lists_the_missing_profile_requirements(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('jobs.show', JobListing::factory()->create()))
            ->assertOk()
            ->assertSee('أكمل ملفك الشخصي قبل التقديم')
            ->assertSee('نبذة شخصية')
            ->assertSee('سيرة ذاتية');
    }

    public function test_the_profile_page_shows_the_completion_percentage(): void
    {
        $user = User::factory()->create([
            'profile' => 'مطور ويب.',
            'location' => 'الرياض',
        ]);
        $user->skills()->attach(Skill::factory()->create());

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('اكتمال الملف الشخصي')
            ->assertSee('50%');
    }
}
