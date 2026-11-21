<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ApplicationStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationOrderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_created_in_the_same_second_are_listed_newest_first(): void
    {
        $user = User::factory()->create();
        $timestamp = now();

        foreach (['الأول', 'الثاني', 'الثالث'] as $title) {
            $this->createNotification($user, $title, $timestamp);
        }

        $this->actingAs($user);

        Livewire::test('pages::notifications.index')
            ->assertSeeInOrder(['الثالث', 'الثاني', 'الأول']);
    }

    public function test_each_new_notification_receives_an_increasing_sort_order(): void
    {
        $user = User::factory()->create();

        $this->createNotification($user, 'الأول', now());
        $this->createNotification($user, 'الثاني', now());

        $this->assertSame(
            [1, 2],
            $user->notifications()->orderBy('sort_order')->pluck('sort_order')->map(fn ($order) => (int) $order)->all(),
        );
    }

    private function createNotification(User $user, string $title, \DateTimeInterface $createdAt): void
    {
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => ApplicationStatusChanged::class,
            'data' => [
                'title' => $title,
                'body' => 'نص الإشعار '.$title,
            ],
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
