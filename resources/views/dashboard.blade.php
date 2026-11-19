<x-layouts::app :title="'لوحة التحكم'">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        @php($completeness = \App\Support\SeekerProfileCompleteness::for(auth()->user()))

        @if (auth()->user()->isSeeker())
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
        @endif

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
    </div>
</x-layouts::app>
