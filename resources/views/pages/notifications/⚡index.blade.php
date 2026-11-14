<?php

use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('الإشعارات')] class extends Component {
    use WithPagination;

    public function open(string $notificationId): void
    {
        $notification = $this->findNotification($notificationId);

        $notification->markAsRead();

        unset($this->notifications, $this->unreadCount);

        $url = $notification->data['url'] ?? null;

        if (is_string($url) && $url !== '') {
            $this->redirect($url);
        }
    }

    public function markAsRead(string $notificationId): void
    {
        $this->findNotification($notificationId)->markAsRead();

        unset($this->notifications, $this->unreadCount);
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();

        unset($this->notifications, $this->unreadCount);

        Flux::toast(variant: 'success', text: 'تم تحديد جميع الإشعارات كمقروءة.');
    }

    public function delete(string $notificationId): void
    {
        $this->findNotification($notificationId)->delete();

        unset($this->notifications, $this->unreadCount);

        Flux::toast(variant: 'success', text: 'تم حذف الإشعار.');
    }

    /**
     * @return LengthAwarePaginator<int, DatabaseNotification>
     */
    #[Computed]
    public function notifications(): LengthAwarePaginator
    {
        return Auth::user()->notifications()
            ->latest()
            ->paginate(15);
    }

    #[Computed]
    public function unreadCount(): int
    {
        return Auth::user()->unreadNotifications()->count();
    }

    private function findNotification(string $notificationId): DatabaseNotification
    {
        /** @var DatabaseNotification $notification */
        $notification = Auth::user()->notifications()->findOrFail($notificationId);

        return $notification;
    }
}; ?>

<div class="flex w-full flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">الإشعارات</h1>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                آخر التحديثات على طلباتك ووظائفك.
                @if ($this->unreadCount > 0)
                    <span class="font-medium text-neutral-700 dark:text-neutral-200">
                        ({{ $this->unreadCount }} غير مقروء)
                    </span>
                @endif
            </p>
        </div>

        @if ($this->unreadCount > 0)
            <flux:button variant="filled" icon="check" wire:click="markAllAsRead">
                تحديد الكل كمقروء
            </flux:button>
        @endif
    </div>

    <div class="space-y-3">
        @forelse ($this->notifications as $notification)
            @php
                $isUnread = $notification->read_at === null;
                $url = $notification->data['url'] ?? null;
            @endphp

            <article
                class="rounded-xl border p-5 transition {{ $isUnread ? 'border-blue-200 bg-blue-50/60 dark:border-blue-500/40 dark:bg-blue-500/10' : 'border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900' }}"
                wire:key="notification-{{ $notification->id }}"
            >
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-base font-semibold text-neutral-900 dark:text-white">
                                {{ $notification->data['title'] ?? 'إشعار' }}
                            </h2>

                            @if ($isUnread)
                                <flux:badge size="sm" color="blue">جديد</flux:badge>
                            @endif
                        </div>

                        <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-300">
                            {{ $notification->data['body'] ?? '' }}
                        </p>

                        <p class="mt-2 text-xs text-neutral-500 dark:text-neutral-400">
                            {{ $notification->created_at?->format('Y-m-d H:i') }}
                        </p>
                    </div>

                    <div class="flex flex-col items-end gap-2">
                        @if (is_string($url) && $url !== '')
                            <flux:button
                                size="sm"
                                variant="primary"
                                icon="arrow-left"
                                wire:click="open('{{ $notification->id }}')"
                            >
                                عرض
                            </flux:button>
                        @endif

                        <div class="flex items-center gap-2">
                            @if ($isUnread)
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="check"
                                    wire:click="markAsRead('{{ $notification->id }}')"
                                >
                                    مقروء
                                </flux:button>
                            @endif

                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="trash"
                                wire:click="delete('{{ $notification->id }}')"
                                wire:confirm="هل تريد حذف هذا الإشعار؟"
                            >
                                حذف
                            </flux:button>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-neutral-300 bg-white p-10 text-center dark:border-neutral-600 dark:bg-zinc-900">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    لا توجد إشعارات بعد.
                </p>
            </div>
        @endforelse
    </div>

    @if ($this->notifications->hasPages())
        <div class="flex justify-center">
            {{ $this->notifications->links() }}
        </div>
    @endif
</div>
