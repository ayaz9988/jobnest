<?php

use App\Actions\Applications\WithdrawApplication;
use App\Models\Application;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('تفاصيل الطلب')] class extends Component {
    #[Locked]
    public Application $application;

    public function mount(Application $application): void
    {
        abort_unless(Auth::user()->isSeeker(), 403);
        abort_unless($application->user_id === Auth::id(), 403);

        $this->application = $application->load(['jobListing.category', 'jobListing.postedBy', 'resume']);
    }

    public function withdraw(WithdrawApplication $withdrawApplication): void
    {
        if (! $withdrawApplication->handle($this->application)) {
            Flux::toast(variant: 'danger', text: 'لا يمكن سحب هذا الطلب في حالته الحالية.');

            return;
        }

        $this->application->refresh();

        Flux::toast(variant: 'success', text: 'تم سحب طلبك.');
    }
}; ?>

<div class="flex w-full flex-1 flex-col gap-6">
    <div>
        <flux:button :href="route('applications.index')" variant="ghost" size="sm" icon="arrow-right" wire:navigate>
            العودة إلى طلباتي
        </flux:button>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-semibold text-neutral-900 dark:text-white">
                            {{ $application->jobListing?->title ?? 'وظيفة محذوفة' }}
                        </h1>

                        <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                            @if ($application->jobListing?->category)
                                {{ $application->jobListing->category->name }}
                                ·
                            @endif
                            {{ $application->jobListing?->location ?: 'غير محدد' }}
                            @if ($application->jobListing?->postedBy)
                                · {{ $application->jobListing->postedBy->name }}
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <flux:badge :color="\App\Support\JobListingOptions::applicationStatusColor($application->status)">
                            {{ \App\Support\JobListingOptions::applicationStatus($application->status) }}
                        </flux:badge>

                        @if ($application->jobListing)
                            <flux:badge color="blue">
                                {{ \App\Support\JobListingOptions::workplaceType($application->jobListing->workplace_type) }}
                            </flux:badge>

                            <flux:badge>
                                {{ \App\Support\JobListingOptions::employmentType($application->jobListing->employment_type) }}
                            </flux:badge>
                        @endif
                    </div>
                </div>

                <dl class="mt-5 space-y-3 border-t border-neutral-200 pt-5 text-sm dark:border-neutral-700">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-neutral-500 dark:text-neutral-400">تاريخ التقديم</dt>
                        <dd class="font-medium text-neutral-900 dark:text-white">
                            {{ $application->applied_at?->format('Y-m-d') ?? 'غير محدد' }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-neutral-500 dark:text-neutral-400">آخر تحديث</dt>
                        <dd class="font-medium text-neutral-900 dark:text-white">
                            {{ $application->updated_at?->format('Y-m-d') ?? 'غير محدد' }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-neutral-900 dark:text-white">رسالة التقديم</h2>

                @if (filled($application->cover_letter))
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-neutral-700 dark:text-neutral-200">
                        {{ $application->cover_letter }}
                    </p>
                @else
                    <p class="mt-3 text-sm text-neutral-500 dark:text-neutral-400">
                        لم تكتب رسالة تقديم مع هذا الطلب.
                    </p>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-neutral-900 dark:text-white">السيرة الذاتية المرسلة</h2>

                @if ($application->resume)
                    <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-300">
                        {{ $application->resume->file_name }}
                    </p>

                    @if ($application->resume->hasStoredFile())
                        <flux:button
                            :href="route('resumes.download', $application->resume)"
                            size="sm"
                            variant="filled"
                            icon="arrow-down-tray"
                            class="mt-3 w-full"
                        >
                            تحميل السيرة الذاتية
                        </flux:button>
                    @endif
                @else
                    <p class="mt-3 text-sm text-neutral-500 dark:text-neutral-400">
                        لم تُرفق سيرة ذاتية مع هذا الطلب.
                    </p>
                @endif
            </section>

            <section class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-neutral-900 dark:text-white">إدارة الطلب</h2>

                @if ($application->canBeWithdrawn())
                    <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-300">
                        يمكنك سحب طلبك ما دامت حالته لا تزال قابلة للسحب.
                    </p>

                    <flux:button
                        class="mt-3 w-full"
                        variant="danger"
                        icon="x-mark"
                        wire:click="withdraw"
                        wire:confirm="هل تريد سحب هذا الطلب؟"
                    >
                        سحب الطلب
                    </flux:button>
                @elseif ($application->isWithdrawn())
                    <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-300">
                        لقد سحبت هذا الطلب. يمكنك التقديم على الوظيفة مرة أخرى إذا كانت لا تزال مفتوحة.
                    </p>

                    @if ($application->jobListing)
                        <flux:button
                            :href="route('jobs.show', $application->jobListing)"
                            class="mt-3 w-full"
                            variant="primary"
                            icon="arrow-path"
                            wire:navigate
                        >
                            التقديم مرة أخرى
                        </flux:button>
                    @endif
                @else
                    <p class="mt-3 text-sm text-neutral-500 dark:text-neutral-400">
                        لا يمكن سحب هذا الطلب بعد أن وصل إلى حالته الحالية.
                    </p>
                @endif

                @if ($application->jobListing)
                    <flux:button
                        :href="route('jobs.show', $application->jobListing)"
                        class="mt-3 w-full"
                        variant="filled"
                        icon="briefcase"
                        wire:navigate
                    >
                        عرض الوظيفة
                    </flux:button>
                @endif
            </section>
        </aside>
    </div>
</div>
