<?php

use App\Models\Application;
use App\Models\JobListing;
use App\Models\Resume;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('تفاصيل الوظيفة')] class extends Component {
    #[Locked]
    public JobListing $jobListing;

    public string $resumeId = '';

    public string $coverLetter = '';

    public function mount(JobListing $jobListing): void
    {
        abort_if($jobListing->status === 'draft', 404);

        $this->jobListing = $jobListing;

        $primaryResume = Auth::user()->resumes()
            ->orderByDesc('is_primary')
            ->latest()
            ->first();

        $this->resumeId = $primaryResume ? (string) $primaryResume->id : '';
    }

    public function toggleSave(): void
    {
        $user = Auth::user();

        if ($user->savedJobListings()->whereKey($this->jobListing->getKey())->exists()) {
            $user->savedJobListings()->detach($this->jobListing->getKey());

            Flux::toast(variant: 'success', text: 'تمت إزالة الوظيفة من المحفوظات.');

            unset($this->isSaved);

            return;
        }

        $user->savedJobListings()->attach($this->jobListing->getKey());

        unset($this->isSaved);

        Flux::toast(variant: 'success', text: 'تم حفظ الوظيفة.');
    }

    public function apply(): void
    {
        $user = Auth::user();

        if (! $this->jobListing->isOpen()) {
            $this->addError('application', 'هذه الوظيفة لم تعد تستقبل طلبات.');

            return;
        }

        $validated = $this->validate([
            'resumeId' => [
                'required',
                'integer',
                Rule::exists('resumes', 'id')->where(
                    fn ($query) => $query->where('user_id', $user->id)
                ),
            ],
            'coverLetter' => ['nullable', 'string', 'max:2000'],
        ], $this->messages(), [
            'resumeId' => 'السيرة الذاتية',
            'coverLetter' => 'رسالة التقديم',
        ]);

        if ($this->alreadyApplied) {
            $this->addError('application', 'لقد تقدمت لهذه الوظيفة بالفعل.');

            return;
        }

        try {
            Application::query()->create([
                'user_id' => $user->id,
                'job_listing_id' => $this->jobListing->getKey(),
                'resume_id' => (int) $validated['resumeId'],
                'cover_letter' => filled($validated['coverLetter'] ?? null)
                    ? $validated['coverLetter']
                    : null,
                'status' => 'applied',
                'applied_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            $this->addError('application', 'لقد تقدمت لهذه الوظيفة بالفعل.');

            return;
        }

        $this->reset('coverLetter');
        unset($this->alreadyApplied, $this->resumes);

        Flux::toast(variant: 'success', text: 'تم إرسال طلبك بنجاح.');
    }

    /**
     * @return Collection<int, Resume>
     */
    #[Computed]
    public function resumes(): Collection
    {
        return Auth::user()->resumes()
            ->orderByDesc('is_primary')
            ->latest()
            ->get();
    }

    #[Computed]
    public function isSaved(): bool
    {
        return Auth::user()->savedJobListings()
            ->whereKey($this->jobListing->getKey())
            ->exists();
    }

    #[Computed]
    public function alreadyApplied(): ?Application
    {
        return Application::query()
            ->where('user_id', Auth::id())
            ->where('job_listing_id', $this->jobListing->getKey())
            ->first();
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'resumeId.required' => 'اختر سيرة ذاتية للتقديم.',
            'resumeId.integer' => 'السيرة الذاتية المحددة غير صالحة.',
            'resumeId.exists' => 'يجب اختيار سيرة ذاتية من ملفك الشخصي.',
            'coverLetter.max' => 'رسالة التقديم يجب ألا تتجاوز 2000 حرف.',
        ];
    }
}; ?>

<div class="flex w-full flex-1 flex-col gap-6">
    <div>
        <flux:button :href="route('jobs.index')" variant="ghost" size="sm" icon="arrow-right" wire:navigate>
            العودة إلى الوظائف
        </flux:button>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-semibold text-neutral-900 dark:text-white">
                            {{ $jobListing->title }}
                        </h1>

                        <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                            @if ($jobListing->category)
                                {{ $jobListing->category->name }}
                                ·
                            @endif
                            {{ $jobListing->location ?: 'غير محدد' }}
                            @if ($jobListing->postedBy)
                                · {{ $jobListing->postedBy->name }}
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <flux:badge color="blue">
                            {{ \App\Support\JobListingOptions::workplaceType($jobListing->workplace_type) }}
                        </flux:badge>

                        <flux:badge>
                            {{ \App\Support\JobListingOptions::employmentType($jobListing->employment_type) }}
                        </flux:badge>

                        @unless ($jobListing->isOpen())
                            <flux:badge color="red">مغلقة</flux:badge>
                        @endunless
                    </div>
                </div>

                <div class="mt-5 border-t border-neutral-200 pt-5 text-sm leading-7 text-neutral-700 dark:border-neutral-700 dark:text-neutral-200">
                    {!! nl2br(e($jobListing->description)) !!}
                </div>
            </section>

            <section class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-neutral-900 dark:text-white">المهارات المطلوبة</h2>

                @if ($jobListing->skills->isEmpty())
                    <p class="mt-3 text-sm text-neutral-500 dark:text-neutral-400">
                        لم تُحدد مهارات لهذه الوظيفة.
                    </p>
                @else
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($jobListing->skills as $skill)
                            <flux:badge :color="$skill->pivot->is_required ? 'amber' : 'zinc'">
                                {{ $skill->name }}
                                @if ($skill->pivot->is_required)
                                    (مطلوبة)
                                @endif
                            </flux:badge>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-neutral-500 dark:text-neutral-400">الراتب</dt>
                        <dd class="font-medium text-neutral-900 dark:text-white">
                            @if ($jobListing->salary_min || $jobListing->salary_max)
                                {{ $jobListing->salary_min ? number_format($jobListing->salary_min) : '—' }}
                                -
                                {{ $jobListing->salary_max ? number_format($jobListing->salary_max) : '—' }}
                                {{ $jobListing->salary_currency }}
                            @else
                                غير محدد
                            @endif
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-neutral-500 dark:text-neutral-400">تاريخ النشر</dt>
                        <dd class="font-medium text-neutral-900 dark:text-white">
                            {{ ($jobListing->published_at ?? $jobListing->created_at)?->format('Y-m-d') }}
                        </dd>
                    </div>

                    @if ($jobListing->expires_at)
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-neutral-500 dark:text-neutral-400">آخر موعد</dt>
                            <dd class="font-medium text-neutral-900 dark:text-white">
                                {{ $jobListing->expires_at->format('Y-m-d') }}
                            </dd>
                        </div>
                    @endif
                </dl>

                <div class="mt-5">
                    <flux:button
                        class="w-full"
                        :variant="$this->isSaved ? 'primary' : 'filled'"
                        icon="bookmark"
                        wire:click="toggleSave"
                    >
                        {{ $this->isSaved ? 'إزالة من المحفوظات' : 'حفظ الوظيفة' }}
                    </flux:button>
                </div>
            </section>

            <section class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-neutral-900 dark:text-white">التقديم على الوظيفة</h2>

                @if ($this->alreadyApplied)
                    <div class="mt-4 space-y-2">
                        <p class="text-sm text-neutral-600 dark:text-neutral-300">
                            لقد تقدمت لهذه الوظيفة.
                        </p>
                        <flux:badge color="green">
                            {{ \App\Support\JobListingOptions::applicationStatus($this->alreadyApplied->status) }}
                        </flux:badge>
                    </div>
                @elseif ($this->resumes->isEmpty())
                    <div class="mt-4 space-y-3">
                        <p class="text-sm text-neutral-600 dark:text-neutral-300">
                            تحتاج إلى رفع سيرة ذاتية قبل التقديم على الوظائف.
                        </p>
                        <flux:button :href="route('profile.show')" size="sm" variant="primary" wire:navigate>
                            رفع سيرة ذاتية
                        </flux:button>
                    </div>
                @else
                    <form wire:submit="apply" class="mt-4 space-y-4">
                        <flux:select wire:model="resumeId" label="السيرة الذاتية">
                            @foreach ($this->resumes as $resume)
                                <flux:select.option value="{{ $resume->id }}">
                                    {{ $resume->file_name }}{{ $resume->is_primary ? ' (الرئيسية)' : '' }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:textarea
                            wire:model="coverLetter"
                            label="رسالة التقديم (اختياري)"
                            rows="4"
                            placeholder="اكتب رسالة قصيرة تعرّف فيها بنفسك..."
                        />

                        @error('application')
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        <flux:button
                            type="submit"
                            variant="primary"
                            class="w-full"
                            wire:loading.attr="disabled"
                            wire:target="apply"
                        >
                            إرسال الطلب
                        </flux:button>
                    </form>
                @endif
            </section>
        </aside>
    </div>
</div>
