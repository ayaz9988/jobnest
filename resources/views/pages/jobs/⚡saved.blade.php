<?php

use App\Models\JobListing;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('الوظائف المحفوظة')] class extends Component {
    use WithPagination;

    public function remove(int $jobListingId): void
    {
        Auth::user()->savedJobListings()->detach($jobListingId);

        unset($this->savedJobs);

        Flux::toast(variant: 'success', text: 'تمت إزالة الوظيفة من المحفوظات.');
    }

    /**
     * @return LengthAwarePaginator<int, JobListing>
     */
    #[Computed]
    public function savedJobs(): LengthAwarePaginator
    {
        return Auth::user()->savedJobListings()
            ->with(['category', 'skills', 'postedBy'])
            ->orderByDesc('saved_job_listings.created_at')
            ->paginate(10);
    }
}; ?>

<div class="flex w-full flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">الوظائف المحفوظة</h1>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                الوظائف التي حفظتها للرجوع إليها لاحقًا.
            </p>
        </div>

        <flux:button :href="route('jobs.index')" variant="filled" icon="briefcase" wire:navigate>
            تصفح الوظائف
        </flux:button>
    </div>

    <div class="space-y-4">
        @forelse ($this->savedJobs as $job)
            <article
                class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900"
                wire:key="saved-job-{{ $job->id }}"
            >
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:link
                                href="{{ route('jobs.show', $job) }}"
                                class="text-lg font-semibold text-neutral-900 dark:text-white"
                                wire:navigate
                            >
                                {{ $job->title }}
                            </flux:link>

                            <flux:badge size="sm" color="blue">
                                {{ \App\Support\JobListingOptions::workplaceType($job->workplace_type) }}
                            </flux:badge>

                            <flux:badge size="sm">
                                {{ \App\Support\JobListingOptions::employmentType($job->employment_type) }}
                            </flux:badge>

                            @unless ($job->isOpen())
                                <flux:badge size="sm" color="red">مغلقة</flux:badge>
                            @endunless
                        </div>

                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                            @if ($job->category)
                                {{ $job->category->name }}
                                ·
                            @endif
                            {{ $job->location ?: 'غير محدد' }}
                        </p>
                    </div>

                    <flux:button
                        size="sm"
                        variant="danger"
                        icon="trash"
                        wire:click="remove({{ $job->id }})"
                        wire:confirm="هل تريد إزالة هذه الوظيفة من المحفوظات؟"
                    >
                        إزالة
                    </flux:button>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-neutral-300 bg-white p-10 text-center dark:border-neutral-600 dark:bg-zinc-900">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    لم تحفظ أي وظيفة بعد.
                </p>
                <flux:button :href="route('jobs.index')" size="sm" variant="primary" class="mt-4" wire:navigate>
                    تصفح الوظائف المتاحة
                </flux:button>
            </div>
        @endforelse
    </div>

    @if ($this->savedJobs->hasPages())
        <div class="flex justify-center">
            {{ $this->savedJobs->links() }}
        </div>
    @endif
</div>
