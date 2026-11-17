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
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">الوظائف عن بُعد</h1>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                ابحث عن الوظيفة المناسبة لك وطبّق بسيرتك الذاتية في دقيقة.
            </p>
        </div>

        <flux:button :href="route('jobs.saved')" variant="filled" icon="bookmark" wire:navigate>
            الوظائف المحفوظة
        </flux:button>
    </div>

    <section class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
            />

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

        <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                {{ $this->jobs->total() }} وظيفة متاحة
            </p>

            <flux:button size="sm" variant="ghost" wire:click="resetFilters">
                مسح الفلاتر
            </flux:button>
        </div>
    </section>

    <div class="space-y-4">
        @forelse ($this->jobs as $job)
            <article
                class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900"
                wire:key="job-{{ $job->id }}"
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
                        </div>

                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                            @if ($job->category)
                                {{ $job->category->name }}
                                ·
                            @endif
                            {{ $job->location ?: 'غير محدد' }}
                            @if ($job->postedBy)
                                · نُشرت بواسطة {{ $job->postedBy->name }}
                            @endif
                        </p>

                        <p class="mt-3 line-clamp-2 text-sm text-neutral-600 dark:text-neutral-300">
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
                    </div>

                    <div class="flex flex-col items-end gap-2">
                        @if ($job->salary_min || $job->salary_max)
                            <span class="text-sm font-medium text-neutral-900 dark:text-white">
                                {{ $job->salary_min ? number_format($job->salary_min) : '—' }}
                                -
                                {{ $job->salary_max ? number_format($job->salary_max) : '—' }}
                                {{ $job->salary_currency }}
                            </span>
                        @endif

                        @if (auth()->user()->isSeeker())
                            <flux:button
                                size="sm"
                                :variant="in_array($job->id, $this->savedIds, true) ? 'primary' : 'filled'"
                                icon="bookmark"
                                wire:click="toggleSave({{ $job->id }})"
                            >
                                {{ in_array($job->id, $this->savedIds, true) ? 'محفوظة' : 'حفظ' }}
                            </flux:button>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-neutral-300 bg-white p-10 text-center dark:border-neutral-600 dark:bg-zinc-900">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    لا توجد وظائف مطابقة لبحثك حاليًا.
                </p>
            </div>
        @endforelse
    </div>

    @if ($this->jobs->hasPages())
        <div class="flex justify-center">
            {{ $this->jobs->links() }}
        </div>
    @endif
</div>
