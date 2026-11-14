<?php

namespace Tests\Feature;

use App\Models\Resume;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SeekerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('profile.show'))->assertRedirect(route('login'));
    }

    public function test_profile_page_is_displayed_for_authenticated_users(): void
    {
        $user = User::factory()->create(['name' => 'سارة أحمد']);

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('ملفي الشخصي')
            ->assertSee('سارة أحمد');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::profile.edit')
            ->set('name', 'محمد علي')
            ->set('profile', 'مطور ويب بخبرة خمس سنوات.')
            ->set('location', 'الرياض')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertSame('محمد علي', $user->name);
        $this->assertSame('مطور ويب بخبرة خمس سنوات.', $user->profile);
        $this->assertSame('الرياض', $user->location);
    }

    public function test_profile_name_is_required(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::profile.edit')
            ->set('name', '')
            ->call('updateProfile')
            ->assertHasErrors(['name']);
    }

    public function test_skill_can_be_added_with_proficiency_and_experience(): void
    {
        $user = User::factory()->create();
        $skill = Skill::factory()->create(['name' => 'Laravel']);

        $this->actingAs($user);

        Livewire::test('pages::profile.edit')
            ->set('newSkillId', (string) $skill->id)
            ->set('newProficiency', 'advanced')
            ->set('newYears', '3')
            ->call('addSkill')
            ->assertHasNoErrors()
            ->assertSet("proficiencies.{$skill->id}", 'advanced')
            ->assertSet("years.{$skill->id}", '3');

        $this->assertDatabaseHas('user_skill', [
            'user_id' => $user->id,
            'skill_id' => $skill->id,
            'proficiency' => 'advanced',
            'years_of_experience' => 3,
        ]);
    }

    public function test_duplicate_skill_cannot_be_added(): void
    {
        $user = User::factory()->create();
        $skill = Skill::factory()->create();
        $user->skills()->attach($skill->id);

        $this->actingAs($user);

        Livewire::test('pages::profile.edit')
            ->set('newSkillId', (string) $skill->id)
            ->call('addSkill')
            ->assertHasErrors(['newSkillId']);

        $this->assertSame(1, $user->skills()->count());
    }

    public function test_skill_proficiency_can_be_updated(): void
    {
        $user = User::factory()->create();
        $skill = Skill::factory()->create();
        $user->skills()->attach($skill->id, ['proficiency' => 'beginner', 'years_of_experience' => 1]);

        $this->actingAs($user);

        Livewire::test('pages::profile.edit')
            ->set("proficiencies.{$skill->id}", 'expert')
            ->set("years.{$skill->id}", '7')
            ->call('updateSkill', $skill->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('user_skill', [
            'user_id' => $user->id,
            'skill_id' => $skill->id,
            'proficiency' => 'expert',
            'years_of_experience' => 7,
        ]);
    }

    public function test_skill_can_be_removed(): void
    {
        $user = User::factory()->create();
        $skill = Skill::factory()->create();
        $user->skills()->attach($skill->id);

        $this->actingAs($user);

        Livewire::test('pages::profile.edit')
            ->call('removeSkill', $skill->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('user_skill', [
            'user_id' => $user->id,
            'skill_id' => $skill->id,
        ]);
    }

    public function test_resume_can_be_uploaded(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->createWithContent(
            'my-cv.pdf',
            "%PDF-1.4\n% fake resume\n%%EOF"
        );

        Livewire::test('pages::profile.edit')
            ->set('resumeFile', $file)
            ->call('uploadResume')
            ->assertHasNoErrors();

        $resume = $user->resumes()->firstOrFail();

        $this->assertSame('my-cv.pdf', $resume->file_name);
        $this->assertTrue($resume->is_primary);
        Storage::disk('local')->assertExists($resume->file_path);
    }

    public function test_only_allowed_resume_file_types_are_accepted(): void
    {
        Storage::fake('local');

        $this->actingAs(User::factory()->create());

        $file = UploadedFile::fake()->create('malware.exe', 10, 'application/x-msdownload');

        Livewire::test('pages::profile.edit')
            ->set('resumeFile', $file)
            ->call('uploadResume')
            ->assertHasErrors(['resumeFile']);
    }

    public function test_primary_resume_can_be_changed(): void
    {
        $user = User::factory()->create();
        $first = Resume::factory()->primary()->create(['user_id' => $user->id]);
        $second = Resume::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test('pages::profile.edit')
            ->call('setPrimaryResume', $second->id)
            ->assertHasNoErrors();

        $this->assertFalse($first->refresh()->is_primary);
        $this->assertTrue($second->refresh()->is_primary);
    }

    public function test_resume_can_be_deleted_and_primary_is_promoted(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $path = 'resumes/'.$user->id.'/old-cv.pdf';
        Storage::disk('local')->put($path, 'pdf-content');

        $primary = Resume::factory()->primary()->create([
            'user_id' => $user->id,
            'file_path' => $path,
        ]);
        $replacement = Resume::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test('pages::profile.edit')
            ->call('deleteResume', $primary->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('resumes', ['id' => $primary->id]);
        Storage::disk('local')->assertMissing($path);
        $this->assertTrue($replacement->refresh()->is_primary);
    }

    public function test_resume_can_be_downloaded_by_its_owner_only(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $other = User::factory()->create();
        $path = 'resumes/'.$owner->id.'/cv.pdf';
        Storage::disk('local')->put($path, 'pdf-content');

        $resume = Resume::factory()->create([
            'user_id' => $owner->id,
            'file_path' => $path,
        ]);

        $this->actingAs($owner)
            ->get(route('resumes.download', $resume))
            ->assertOk();

        $this->actingAs($other)
            ->get(route('resumes.download', $resume))
            ->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_resume(): void
    {
        $user = User::factory()->create();
        $foreignResume = Resume::factory()->create();

        $this->actingAs($user);

        try {
            Livewire::test('pages::profile.edit')->call('deleteResume', $foreignResume->id);
        } catch (ModelNotFoundException) {
            // Livewire may surface the scoped lookup failure as a 404; either way nothing is deleted.
        }

        $this->assertDatabaseHas('resumes', ['id' => $foreignResume->id]);
    }
}
