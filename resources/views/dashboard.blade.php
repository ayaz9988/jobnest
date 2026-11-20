<x-layouts::app :title="'لوحة التحكم'">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        @php
            $user = auth()->user();
            $completeness = \App\Support\SeekerProfileCompleteness::for($user);
            $isSeeker = $user->isSeeker();

            if ($isSeeker) {
                $applications = $user->applications()
                    ->with('jobListing')
                    ->latest('applied_at')
                    ->take(4)
                    ->get();

                $stats = [
                    'active' => $user->applications()
                        ->whereIn('status', [
                            \App\Models\Application::STATUS_APPLIED,
                            \App\Models\Application::STATUS_REVIEWED,
                            \App\Models\Application::STATUS_INTERVIEW,
                        ])
                        ->count(),
                    'interviews' => $user->applications()
                        ->where('status', \App\Models\Application::STATUS_INTERVIEW)
                        ->count(),
                    'hired' => $user->applications()
                        ->where('status', \App\Models\Application::STATUS_HIRED)
                        ->count(),
                    'saved' => $user->savedJobListings()->count(),
                ];

                $latestJobs = \App\Models\JobListing::query()
                    ->open()
                    ->with('postedBy')
                    ->latest('published_at')
                    ->take(4)
                    ->get();
            }
        @endphp

        @if ($isSeeker)
            <section class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-neutral-900 dark:text-white">اكتمال ملفك الشخصي</h2>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                            @if ($completeness->canApply())
                                ملفك جاهز للتقديم على الوظائف.
                            @else
                                أكمل بياناتك لتتمكن من التقديم على الوظائف.
                            @endif
                        </p>
                    </div>

                    <span class="text-lg font-semibold text-neutral-900 dark:text-white">
                        {{ $completeness->percentage() }}%
                    </span>
                </div>

                <flux:progress class="mt-3" color="blue" :value="$completeness->percentage()" />

                @unless ($completeness->canApply())
                    <p class="mt-3 text-sm text-amber-800 dark:text-amber-200">
                        متبقٍ: {{ implode('، ', $completeness->missingForApplying()) }}.
                    </p>
                @endunless

                <div class="mt-4">
                    <flux:button :href="route('profile.show')" size="sm" variant="primary" icon="user" wire:navigate>
                        {{ $completeness->canApply() ? 'تحديث الملف الشخصي' : 'إكمال الملف الشخصي' }}
                    </flux:button>
                </div>
            </section>

            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                @foreach ([
                    ['label' => 'طلبات قيد المتابعة', 'value' => $stats['active'], 'icon' => 'paper-airplane'],
                    ['label' => 'المقابلات', 'value' => $stats['interviews'], 'icon' => 'calendar-days'],
                    ['label' => 'تم التوظيف', 'value' => $stats['hired'], 'icon' => 'check-badge'],
                    ['label' => 'وظائف محفوظة', 'value' => $stats['saved'], 'icon' => 'bookmark'],
                ] as $stat)
                    <div class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-2xl font-semibold text-neutral-900 dark:text-white">
                                {{ $stat['value'] }}
                            </span>

                            <flux:icon :name="$stat['icon']" class="size-5 text-neutral-400 dark:text-neutral-500" />
                        </div>

                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                            {{ $stat['label'] }}
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <section class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-semibold text-neutral-900 dark:text-white">أحدث طلباتي</h2>

                        <flux:button :href="route('applications.index')" size="sm" variant="ghost" icon="arrow-left" wire:navigate>
                            عرض الكل
                        </flux:button>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse ($applications as $application)
                            <a
                                href="{{ route('applications.show', $application) }}"
                                wire:navigate
                                class="flex items-center justify-between gap-3 rounded-lg border border-neutral-200 p-3 transition hover:bg-neutral-50 dark:border-neutral-700 dark:hover:bg-neutral-800"
                            >
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-neutral-900 dark:text-white">
                                        {{ $application->jobListing?->title ?? 'وظيفة محذوفة' }}
                                    </p>

                                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                        {{ $application->applied_at?->format('Y-m-d') ?? 'غير محدد' }}
                                    </p>
                                </div>

                                <flux:badge :color="\App\Support\JobListingOptions::applicationStatusColor($application->status)">
                                    {{ \App\Support\JobListingOptions::applicationStatus($application->status) }}
                                </flux:badge>
                            </a>
                        @empty
                            <div class="rounded-lg border border-dashed border-neutral-300 p-6 text-center dark:border-neutral-700">
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                    لم تتقدم على أي وظيفة بعد.
                                </p>

                                <flux:button :href="route('jobs.index')" size="sm" variant="primary" class="mt-3" wire:navigate>
                                    تصفح الوظائف
                                </flux:button>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-semibold text-neutral-900 dark:text-white">وظائف حديثة</h2>

                        <flux:button :href="route('jobs.index')" size="sm" variant="ghost" icon="arrow-left" wire:navigate>
                            عرض الكل
                        </flux:button>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse ($latestJobs as $job)
                            <a
                                href="{{ route('jobs.show', $job) }}"
                                wire:navigate
                                class="block rounded-lg border border-neutral-200 p-3 transition hover:bg-neutral-50 dark:border-neutral-700 dark:hover:bg-neutral-800"
                            >
                                <p class="truncate text-sm font-medium text-neutral-900 dark:text-white">
                                    {{ $job->title }}
                                </p>

                                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                    {{ $job->postedBy?->name ?? 'شركة غير محددة' }}
                                    · {{ $job->location ?: 'غير محدد' }}
                                </p>
                            </a>
                        @empty
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                لا توجد وظائف متاحة حالياً.
                            </p>
                        @endforelse
                    </div>
                </section>
            </div>
        @else
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
                </div>
            </div>
            <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
        @endif
    </div>
</x-layouts::app>
