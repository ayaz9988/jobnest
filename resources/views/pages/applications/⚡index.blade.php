<?php

use App\Actions\Applications\WithdrawApplication;
use App\Models\Application;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('طلباتي')] class extends Component {
    use WithPagination;

    public string $status = '';

    public function mount(): void
    {
        abort_unless(Auth::user()->isSeeker(), 403);
    }

    public function updated(string $property): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('status');
        $this->resetPage();
    }

    public function withdraw(int $applicationId, WithdrawApplication $withdrawApplication): void
    {
        $application = Auth::user()->applications()->findOrFail($applicationId);

        if (! $withdrawApplication->handle($application)) {
            Flux::toast(variant: 'danger', text: 'لا يمكن سحب هذا الطلب في حالته الحالية.');

            return;
        }

        unset($this->applications);

        Flux::toast(variant: 'success', text: 'تم سحب طلبك.');
    }

    /**
     * @return LengthAwarePaginator<int, Application>
     */
    #[Computed]
    public function applications(): LengthAwarePaginator
    {
        return Auth::user()->applications()
            ->with(['jobListing.category', 'jobListing.postedBy', 'resume'])
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->orderByDesc('applied_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();
    }
}; ?>

<div class="flex w-full flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">طلباتي</h1>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                تابع حالة طلباتك على الوظائف التي تقدمت إليها.
            </p>
        </div>

        <flux:button :href="route('jobs.index')" variant="filled" icon="briefcase" wire:navigate>
            تصفح الوظائف
        </flux:button>
    </div>

    <section class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <flux:select wire:model.live="status" label="الحالة" placeholder="جميع الحالات" class="max-w-xs">
                @foreach (\App\Support\JobListingOptions::applicationStatuses() as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex items-center gap-2">
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    {{ $this->applications->total() }} طلب
                </p>

                <flux:button size="sm" variant="ghost" wire:click="resetFilters">
                    مسح الفلاتر
                </flux:button>
            </div>
        </div>
    </section>

    <div class="space-y-4">
        @forelse ($this->applications as $application)
            <article
                class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900"
                wire:key="application-{{ $application->id }}"
            >
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:link
                                href="{{ route('applications.show', $application) }}"
                                class="text-lg font-semibold text-neutral-900 dark:text-white"
                                wire:navigate
                            >
                                {{ $application->jobListing?->title ?? 'وظيفة محذوفة' }}
                            </flux:link>

                            <flux:badge size="sm" :color="\App\Support\JobListingOptions::applicationStatusColor($application->status)">
                                {{ \App\Support\JobListingOptions::applicationStatus($application->status) }}
                            </flux:badge>
                        </div>

                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                            @if ($application->jobListing?->category)
                                {{ $application->jobListing->category->name }}
                                ·
                            @endif
                            {{ $application->jobListing?->location ?: 'غير محدد' }}
                            @if ($application->jobListing?->postedBy)
                                · {{ $application->jobListing->postedBy->name }}
                            @endif
                        </p>

                        <p class="mt-2 text-xs text-neutral-500 dark:text-neutral-400">
                            تاريخ التقديم: {{ $application->applied_at?->format('Y-m-d') ?? 'غير محدد' }}
                            @if ($application->resume)
                                · السيرة الذاتية: {{ $application->resume->file_name }}
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-col items-end gap-2">
                        <flux:button
                            :href="route('applications.show', $application)"
                            size="sm"
                            variant="filled"
                            icon="document-text"
                            wire:navigate
                        >
                            التفاصيل
                        </flux:button>

                        @if ($application->canBeWithdrawn())
                            <flux:button
                                size="sm"
                                variant="danger"
                                icon="x-mark"
                                wire:click="withdraw({{ $application->id }})"
                                wire:confirm="هل تريد سحب هذا الطلب؟"
                            >
                                سحب الطلب
                            </flux:button>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-neutral-300 bg-white p-10 text-center dark:border-neutral-600 dark:bg-zinc-900">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    لا توجد طلبات مطابقة حاليًا.
                </p>
                <flux:button :href="route('jobs.index')" size="sm" variant="primary" class="mt-4" wire:navigate>
                    تصفح الوظائف المتاحة
                </flux:button>
            </div>
        @endforelse
    </div>

    @if ($this->applications->hasPages())
        <div class="flex justify-center">
            {{ $this->applications->links() }}
        </div>
    @endif
</div>
