<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\JobListing;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JobBrowsingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $job = JobListing::factory()->create();

        $this->get(route('jobs.index'))->assertRedirect(route('login'));
        $this->get(route('jobs.show', $job))->assertRedirect(route('login'));
        $this->get(route('jobs.saved'))->assertRedirect(route('login'));
    }

    public function test_index_lists_only_open_and_unexpired_listings(): void
    {
        $open = JobListing::factory()->create(['title' => 'وظيفة مفتوحة']);
        $closed = JobListing::factory()->closed()->create(['title' => 'وظيفة مغلقة']);
        $expired = JobListing::factory()->expired()->create(['title' => 'وظيفة منتهية']);
        $draft = JobListing::factory()->create(['title' => 'مسودة', 'status' => 'draft']);
        $future = JobListing::factory()->create([
            'title' => 'وظيفة مستقبلية',
            'published_at' => now()->addDay(),
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('jobs.index'))
            ->assertOk()
            ->assertSee($open->title)
            ->assertDontSee($closed->title)
            ->assertDontSee($expired->title)
            ->assertDontSee($draft->title)
            ->assertDontSee($future->title);
    }

    public function test_search_matches_title_and_description(): void
    {
        $byTitle = JobListing::factory()->create(['title' => 'مطور Laravel']);
        $byDescription = JobListing::factory()->create([
            'title' => 'مهندس برمجيات',
            'description' => 'نبحث عن شخص يجيد Laravel',
        ]);
        $other = JobListing::factory()->create(['title' => 'مصمم واجهات']);

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::jobs.index')
            ->set('search', 'Laravel')
            ->assertSee($byTitle->title)
            ->assertSee($byDescription->title)
            ->assertDontSee($other->title);
    }

    public function test_listings_can_be_filtered_by_category_skill_location_and_types(): void
    {
        $design = Category::factory()->create(['name' => 'التصميم']);
        $development = Category::factory()->create(['name' => 'البرمجة']);
        $laravel = Skill::factory()->create(['name' => 'Laravel']);

        $matching = JobListing::factory()->create([
            'category_id' => $design->id,
            'title' => 'مصمم منتجات',
            'location' => 'الرياض',
            'employment_type' => 'full_time',
            'workplace_type' => 'remote',
        ]);
        $matching->skills()->attach($laravel->id, ['is_required' => true]);

        $wrongCategory = JobListing::factory()->create([
            'category_id' => $development->id,
            'title' => 'مطور خلفية',
            'location' => 'الرياض',
            'employment_type' => 'full_time',
            'workplace_type' => 'remote',
        ]);

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::jobs.index')
            ->set('categoryId', (string) $design->id)
            ->set('skillId', (string) $laravel->id)
            ->set('location', 'الرياض')
            ->set('employmentType', 'full_time')
            ->set('workplaceType', 'remote')
            ->assertSee($matching->title)
            ->assertDontSee($wrongCategory->title);
    }

    public function test_reset_filters_clears_the_search_criteria(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::jobs.index')
            ->set('search', 'Laravel')
            ->set('location', 'الرياض')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('location', '');
    }

    public function test_job_detail_page_shows_the_listing_and_its_skills(): void
    {
        $job = JobListing::factory()->create([
            'title' => 'مطور Laravel عن بُعد',
            'description' => 'وصف تفصيلي للوظيفة.',
        ]);
        $required = Skill::factory()->create(['name' => 'Laravel']);
        $optional = Skill::factory()->create(['name' => 'Docker']);
        $job->skills()->attach($required->id, ['is_required' => true]);
        $job->skills()->attach($optional->id, ['is_required' => false]);

        $this->actingAs(User::factory()->create())
            ->get(route('jobs.show', $job))
            ->assertOk()
            ->assertSee($job->title)
            ->assertSee('وصف تفصيلي للوظيفة.')
            ->assertSee('Laravel')
            ->assertSee('Docker');
    }

    public function test_draft_listings_are_not_publicly_viewable(): void
    {
        $draft = JobListing::factory()->create(['status' => 'draft']);

        $this->actingAs(User::factory()->create())
            ->get(route('jobs.show', $draft))
            ->assertNotFound();
    }

    public function test_job_listing_status_scope_ignores_expired_and_unpublished_records(): void
    {
        JobListing::factory()->create();
        JobListing::factory()->expired()->create();
        JobListing::factory()->create(['published_at' => now()->addHour()]);

        $this->assertSame(1, JobListing::open()->count());
    }

    public function test_listings_can_be_saved_and_unsaved_from_the_index(): void
    {
        $user = User::factory()->create();
        $job = JobListing::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::jobs.index')
            ->call('toggleSave', $job->id);

        $this->assertDatabaseHas('saved_job_listings', [
            'user_id' => $user->id,
            'job_listing_id' => $job->id,
        ]);

        Livewire::test('pages::jobs.index')
            ->call('toggleSave', $job->id);

        $this->assertDatabaseMissing('saved_job_listings', [
            'user_id' => $user->id,
            'job_listing_id' => $job->id,
        ]);
    }

    public function test_saved_jobs_page_only_shows_the_current_users_saved_listings(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $saved = JobListing::factory()->create(['title' => 'وظيفة محفوظة']);
        $notSaved = JobListing::factory()->create(['title' => 'وظيفة غير محفوظة']);
        $otherSaved = JobListing::factory()->create(['title' => 'وظيفة مستخدم آخر']);

        $user->savedJobListings()->attach($saved->id);
        $other->savedJobListings()->attach($otherSaved->id);

        $this->actingAs($user)
            ->get(route('jobs.saved'))
            ->assertOk()
            ->assertSee($saved->title)
            ->assertDontSee($notSaved->title)
            ->assertDontSee($otherSaved->title);
    }

    public function test_a_saved_listing_can_be_removed_from_the_saved_page(): void
    {
        $user = User::factory()->create();
        $job = JobListing::factory()->create();

        $user->savedJobListings()->attach($job->id);

        $this->actingAs($user);

        Livewire::test('pages::jobs.saved')
            ->call('remove', $job->id);

        $this->assertDatabaseMissing('saved_job_listings', [
            'user_id' => $user->id,
            'job_listing_id' => $job->id,
        ]);
    }
}
