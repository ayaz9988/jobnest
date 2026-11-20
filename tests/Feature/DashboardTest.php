<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_the_seeker_dashboard_shows_application_and_job_activity(): void
    {
        $user = User::factory()->create();

        $appliedJob = JobListing::factory()->create(['title' => 'مطور واجهات']);
        Application::query()->create([
            'user_id' => $user->id,
            'job_listing_id' => $appliedJob->id,
            'status' => Application::STATUS_REVIEWED,
            'applied_at' => now(),
        ]);

        JobListing::factory()->create(['title' => 'مهندس بيانات']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('طلبات قيد المتابعة')
            ->assertSee('المقابلات')
            ->assertSee('وظائف محفوظة')
            ->assertSee('أحدث طلباتي')
            ->assertSee('مطور واجهات')
            ->assertSee('قيد المراجعة')
            ->assertSee('وظائف حديثة')
            ->assertSee('مهندس بيانات');
    }

    public function test_the_seeker_dashboard_shows_an_empty_state_with_a_call_to_action(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('لم تتقدم على أي وظيفة بعد.')
            ->assertSee('تصفح الوظائف');
    }

    public function test_employers_do_not_see_the_seeker_dashboard_sections(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'employer']))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('أحدث طلباتي')
            ->assertDontSee('وظائف حديثة');
    }
}
