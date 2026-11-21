<?php

use App\Models\Category;
use App\Models\JobListing;
use App\Models\Skill;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('الوظائف')] class extends Component {
    use WithPagination;

    public string $search = '';

    public string $categoryId = '';

    public string $skillId = '';

    public string $location = '';

    public string $employmentType = '';

    public string $workplaceType = '';

    public function updated(string $property): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'categoryId', 'skillId', 'location', 'employmentType', 'workplaceType');
        $this->resetPage();
    }

    public function toggleSave(int $jobListingId): void
    {
        $user = Auth::user();

        abort_unless($user->isSeeker(), 403);

        $jobListing = JobListing::query()->findOrFail($jobListingId);

        if ($user->savedJobListings()->whereKey($jobListing->getKey())->exists()) {
            $user->savedJobListings()->detach($jobListing->getKey());

            Flux::toast(variant: 'success', text: 'تمت إزالة الوظيفة من المحفوظات.');

            unset($this->savedIds);

            return;
        }

        $user->savedJobListings()->attach($jobListing->getKey());

        unset($this->savedIds);

        Flux::toast(variant: 'success', text: 'تم حفظ الوظيفة.');
    }

    /**
     * @return LengthAwarePaginator<int, JobListing>
     */
    #[Computed]
    public function jobs(): LengthAwarePaginator
    {
        return JobListing::query()
            ->open()
            ->with(['category', 'skills', 'postedBy'])
            ->when($this->search !== '', function ($query): void {
                $term = '%'.trim($this->search).'%';

                $query->where(function ($query) use ($term): void {
                    $query->where('title', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->when($this->categoryId !== '', fn ($query) => $query->where('category_id', (int) $this->categoryId))
            ->when($this->skillId !== '', fn ($query) => $query->whereHas('skills', fn ($query) => $query->whereKey((int) $this->skillId)))
            ->when($this->location !== '', fn ($query) => $query->where('location', 'like', '%'.trim($this->location).'%'))
            ->when($this->employmentType !== '', fn ($query) => $query->where('employment_type', $this->employmentType))
            ->when($this->workplaceType !== '', fn ($query) => $query->where('workplace_type', $this->workplaceType))
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::query()->orderBy('name')->get();
    }

    /**
     * @return Collection<int, Skill>
     */
    #[Computed]
    public function skills(): Collection
    {
        return Skill::query()->orderBy('name')->get();
    }

    /**
     * @return list<int>
     */
    #[Computed]
    public function savedIds(): array
    {
        return Auth::user()->savedJobListings()
            ->pluck('job_listings.id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();
    }
}; ?>

<div class="flex w-full flex-1 flex-col gap-6">
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-bl from-violet-600 via-violet-700 to-indigo-950 px-6 py-8 text-white shadow-lg shadow-violet-900/10 sm:px-8">
        <div class="pointer-events-none absolute -top-16 -start-10 size-48 rounded-full bg-white/10 blur-2xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -end-10 size-56 rounded-full bg-violet-300/20 blur-3xl"></div>

        <div class="relative flex flex-wrap items-end justify-between gap-4">
            <div class="max-w-xl">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-violet-50 ring-1 ring-white/20">
                    <flux:icon.sparkles class="size-3.5" />
                    وظائف جديدة كل يوم
                </span>

                <h1 class="mt-3 text-2xl font-bold leading-snug sm:text-3xl">
                    اعثر على وظيفتك القادمة
                </h1>

                <p class="mt-2 text-sm leading-6 text-violet-50/90">
                    تصفّح وظائف الشركات العربية، احفظ ما يناسبك، وقدّم بسيرتك الذاتية في دقيقة.
                </p>
            </div>

            <a
                href="{{ route('jobs.saved') }}"
                wire:navigate
                class="inline-flex items-center gap-2 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-semibold text-white ring-1 ring-inset ring-white/30 backdrop-blur transition hover:bg-white/25"
            >
                <flux:icon.bookmark class="size-4" />
                الوظائف المحفوظة
            </a>
        </div>
    </section>

    <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-5">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <flux:input
                wire:model.live.debounce.400ms="search"
                label="بحث"
                type="search"
                placeholder="ابحث بعنوان الوظيفة أو الوصف..."
                icon="magnifying-glass"
            />

            <flux:input
                wire:model.live.debounce.400ms="location"
                label="الموقع"
                type="text"
                placeholder="مثال: الرياض، السعودية"
                icon="map-pin"
            />
        </div>

        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <flux:select wire:model.live="categoryId" label="التصنيف" placeholder="جميع التصنيفات">
                @foreach ($this->categories as $category)
                    <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="skillId" label="المهارة" placeholder="جميع المهارات">
                @foreach ($this->skills as $skill)
                    <flux:select.option value="{{ $skill->id }}">{{ $skill->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="employmentType" label="نوع الدوام" placeholder="جميع الأنواع">
                @foreach (\App\Support\JobListingOptions::employmentTypes() as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="workplaceType" label="مكان العمل" placeholder="جميع الأماكن">
                @foreach (\App\Support\JobListingOptions::workplaceTypes() as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-neutral-100 pt-3 dark:border-zinc-800">
            <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400">
                {{ number_format($this->jobs->total()) }} وظيفة متاحة
            </p>

            <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="resetFilters">
                مسح الفلاتر
            </flux:button>
        </div>
    </section>

    <div class="grid auto-rows-fr gap-4 lg:grid-cols-2">
        @forelse ($this->jobs as $job)
            <article
                class="flex h-full flex-col rounded-2xl border border-neutral-200 bg-white p-5 transition hover:border-violet-300 hover:shadow-lg hover:shadow-violet-900/5 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-violet-700/60"
                wire:key="job-{{ $job->id }}"
            >
                <div class="flex items-start gap-3">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-700 dark:bg-violet-500/10 dark:text-violet-300">
                        @if ($job->postedBy)
                            <span class="text-sm font-semibold">{{ $job->postedBy->initials() }}</span>
                        @else
                            <flux:icon.building-office-2 class="size-5" />
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:link
                                href="{{ route('jobs.show', $job) }}"
                                class="text-lg font-semibold text-neutral-900 dark:text-white"
                                wire:navigate
                            >
                                {{ $job->title }}
                            </flux:link>

                            <flux:badge size="sm" color="violet">
                                {{ \App\Support\JobListingOptions::workplaceType($job->workplace_type) }}
                            </flux:badge>

                            <flux:badge size="sm">
                                {{ \App\Support\JobListingOptions::employmentType($job->employment_type) }}
                            </flux:badge>
                        </div>

                        <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-neutral-500 dark:text-neutral-400">
                            @if ($job->postedBy)
                                <span class="inline-flex items-center gap-1">
                                    <flux:icon.building-office-2 class="size-3.5" />
                                    {{ $job->postedBy->name }}
                                </span>
                            @endif

                            <span class="inline-flex items-center gap-1">
                                <flux:icon.map-pin class="size-3.5" />
                                {{ $job->location ?: 'غير محدد' }}
                            </span>

                            @if ($job->published_at)
                                <span class="inline-flex items-center gap-1">
                                    <flux:icon.clock class="size-3.5" />
                                    {{ $job->published_at->locale('ar')->diffForHumans() }}
                                </span>
                            @endif
                        </div>
                    </div>

                    @if (auth()->user()->isSeeker())
                        @php($isSaved = in_array($job->id, $this->savedIds, true))

                        <flux:button
                            size="sm"
                            :variant="$isSaved ? 'primary' : 'ghost'"
                            icon="bookmark"
                            wire:click="toggleSave({{ $job->id }})"
                            :aria-label="$isSaved ? 'إزالة الوظيفة من المحفوظات' : 'حفظ الوظيفة'"
                        >
                            <span class="max-sm:hidden">{{ $isSaved ? 'محفوظة' : 'حفظ' }}</span>
                        </flux:button>
                    @endif
                </div>

                <p class="mt-3 line-clamp-2 text-sm leading-6 text-neutral-600 dark:text-neutral-300">
                    {{ \Illuminate\Support\Str::limit($job->description, 180) }}
                </p>

                @if ($job->skills->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach ($job->skills as $skill)
                            <flux:badge size="sm" :color="$skill->pivot->is_required ? 'amber' : 'zinc'">
                                {{ $skill->name }}
                            </flux:badge>
                        @endforeach
                    </div>
                @endif

                <div class="mt-4 flex-1"></div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-neutral-100 pt-4 dark:border-zinc-800">
                    <div class="flex items-center gap-1.5 text-sm text-neutral-700 dark:text-neutral-200">
                        <flux:icon.banknotes class="size-4 text-neutral-400" />

                        @if ($job->salary_min || $job->salary_max)
                            <span class="font-semibold" dir="ltr">
                                {{ $job->salary_min ? number_format($job->salary_min) : '—' }}
                                -
                                {{ $job->salary_max ? number_format($job->salary_max) : '—' }}
                                <span class="text-xs font-normal text-neutral-500">{{ $job->salary_currency }}</span>
                            </span>
                        @else
                            <span class="text-xs text-neutral-500">الراتب غير محدد</span>
                        @endif
                    </div>

                    <a
                        href="{{ route('jobs.show', $job) }}"
                        wire:navigate
                        class="inline-flex items-center gap-1 text-sm font-semibold text-violet-700 transition hover:text-violet-800 dark:text-violet-400 dark:hover:text-violet-300"
                    >
                        عرض التفاصيل
                        <flux:icon.arrow-left class="size-4" />
                    </a>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-neutral-300 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-neutral-100 text-neutral-400 dark:bg-zinc-800">
                    <flux:icon.magnifying-glass class="size-6" />
                </div>

                <h2 class="mt-4 text-base font-semibold text-neutral-900 dark:text-white">
                    لا توجد وظائف مطابقة
                </h2>

                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    جرّب تعديل كلمات البحث أو مسح الفلاتر لعرض جميع الوظائف.
                </p>

                <flux:button class="mt-4" size="sm" icon="arrow-path" wire:click="resetFilters">
                    مسح الفلاتر
                </flux:button>
            </div>
        @endforelse
    </div>

    @if ($this->jobs->hasPages())
        <div class="flex justify-center">
            {{ $this->jobs->links() }}
        </div>
    @endif
</div>
