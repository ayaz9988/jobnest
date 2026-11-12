<?php

use App\Models\Skill;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new #[Title('ملفي الشخصي')] class extends Component {
    use WithFileUploads;

    private const PROFICIENCIES = ['beginner', 'intermediate', 'advanced', 'expert'];

    public string $name = '';

    public string $profile = '';

    public string $location = '';

    public ?string $newSkillId = null;

    public ?string $newProficiency = null;

    public ?string $newYears = null;

    /** @var array<int|string, string|null> */
    public array $proficiencies = [];

    /** @var array<int|string, string|null> */
    public array $years = [];

    /** @var TemporaryUploadedFile|null */
    public $resumeFile = null;

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->profile = $user->profile ?? '';
        $this->location = $user->location ?? '';

        foreach ($user->skills as $skill) {
            $this->proficiencies[$skill->id] = $skill->pivot->getAttribute('proficiency');

            $years = $skill->pivot->getAttribute('years_of_experience');
            $this->years[$skill->id] = $years !== null ? (string) $years : null;
        }
    }

    public function updateProfile(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'profile' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:100'],
        ], $this->messages(), [
            'name' => 'الاسم',
            'profile' => 'النبذة',
            'location' => 'الموقع',
        ]);

        Auth::user()->update($validated);

        Flux::toast(variant: 'success', text: 'تم حفظ بياناتك.');
    }

    public function addSkill(): void
    {
        $this->normalizeNewSkill();

        $validated = $this->validate([
            'newSkillId' => ['required', 'integer', 'exists:skills,id'],
            'newProficiency' => ['nullable', Rule::in(self::PROFICIENCIES)],
            'newYears' => ['nullable', 'integer', 'min:0', 'max:60'],
        ], $this->messages(), [
            'newSkillId' => 'المهارة',
            'newProficiency' => 'المستوى',
            'newYears' => 'سنوات الخبرة',
        ]);

        $user = Auth::user();
        $skillId = (int) $validated['newSkillId'];

        if ($user->skills()->whereKey($skillId)->exists()) {
            $this->addError('newSkillId', 'هذه المهارة مضافة بالفعل.');

            return;
        }

        $user->skills()->attach($skillId, [
            'proficiency' => $validated['newProficiency'] ?? null,
            'years_of_experience' => isset($validated['newYears']) ? (int) $validated['newYears'] : null,
        ]);

        $this->proficiencies[$skillId] = $validated['newProficiency'] ?? null;
        $this->years[$skillId] = isset($validated['newYears']) ? (string) (int) $validated['newYears'] : null;

        $this->reset('newSkillId', 'newProficiency', 'newYears');
        unset($this->userSkills, $this->availableSkills);

        Flux::toast(variant: 'success', text: 'تمت إضافة المهارة.');
    }

    public function updateSkill(int $skillId): void
    {
        $user = Auth::user();

        if (! $user->skills()->whereKey($skillId)->exists()) {
            return;
        }

        if (($this->proficiencies[$skillId] ?? '') === '') {
            $this->proficiencies[$skillId] = null;
        }

        if (($this->years[$skillId] ?? '') === '') {
            $this->years[$skillId] = null;
        }

        $validated = $this->validate([
            "proficiencies.{$skillId}" => ['nullable', Rule::in(self::PROFICIENCIES)],
            "years.{$skillId}" => ['nullable', 'integer', 'min:0', 'max:60'],
        ], $this->messages(), [
            "proficiencies.{$skillId}" => 'المستوى',
            "years.{$skillId}" => 'سنوات الخبرة',
        ]);

        $proficiency = $validated['proficiencies'][$skillId] ?? null;
        $years = $validated['years'][$skillId] ?? null;

        $user->skills()->updateExistingPivot($skillId, [
            'proficiency' => $proficiency,
            'years_of_experience' => $years !== null ? (int) $years : null,
        ]);

        Flux::toast(variant: 'success', text: 'تم تحديث المهارة.');
    }

    public function removeSkill(int $skillId): void
    {
        Auth::user()->skills()->detach($skillId);

        unset($this->proficiencies[$skillId], $this->years[$skillId]);
        unset($this->userSkills, $this->availableSkills);

        Flux::toast(variant: 'success', text: 'تمت إزالة المهارة.');
    }

    public function uploadResume(): void
    {
        $this->validate([
            'resumeFile' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ], $this->messages(), [
            'resumeFile' => 'ملف السيرة الذاتية',
        ]);

        $user = Auth::user();
        $file = $this->resumeFile;

        if (! $file instanceof TemporaryUploadedFile) {
            return;
        }

        $path = $file->store("resumes/{$user->id}", 'local');

        if (! is_string($path)) {
            $this->addError('resumeFile', 'تعذر رفع الملف. حاول مرة أخرى.');

            return;
        }

        $user->resumes()->create([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => (int) $file->getSize(),
            'is_primary' => ! $user->resumes()->where('is_primary', true)->exists(),
        ]);

        $this->reset('resumeFile');
        unset($this->userResumes);

        Flux::toast(variant: 'success', text: 'تم رفع السيرة الذاتية.');
    }

    public function setPrimaryResume(int $resumeId): void
    {
        $user = Auth::user();
        $resume = $user->resumes()->findOrFail($resumeId);

        DB::transaction(function () use ($user, $resume): void {
            $user->resumes()->whereKeyNot($resume->getKey())->update(['is_primary' => false]);
            $resume->update(['is_primary' => true]);
        });

        unset($this->userResumes);

        Flux::toast(variant: 'success', text: 'تم تعيين السيرة الرئيسية.');
    }

    public function deleteResume(int $resumeId): void
    {
        $user = Auth::user();
        $resume = $user->resumes()->findOrFail($resumeId);

        $wasPrimary = $resume->is_primary;
        $resume->delete();

        if ($wasPrimary) {
            $user->resumes()->latest()->first()?->update(['is_primary' => true]);
        }

        unset($this->userResumes);

        Flux::toast(variant: 'success', text: 'تم حذف السيرة الذاتية.');
    }

    /**
     * @return Collection<int, Skill>
     */
    #[Computed]
    public function userSkills(): Collection
    {
        return Auth::user()->skills()->orderBy('name')->get();
    }

    /**
     * @return Collection<int, Skill>
     */
    #[Computed]
    public function availableSkills(): Collection
    {
        return Skill::query()
            ->whereNotIn('id', $this->userSkills->pluck('id'))
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, \App\Models\Resume>
     */
    #[Computed]
    public function userResumes(): Collection
    {
        return Auth::user()->resumes()->latest()->get();
    }

    private function normalizeNewSkill(): void
    {
        if ($this->newProficiency === '') {
            $this->newProficiency = null;
        }

        if ($this->newYears === '') {
            $this->newYears = null;
        }
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب.',
            'name.max' => 'الاسم يجب ألا يتجاوز 255 حرفًا.',
            'profile.max' => 'النبذة يجب ألا تتجاوز 2000 حرف.',
            'location.max' => 'الموقع يجب ألا يتجاوز 100 حرف.',
            'newSkillId.required' => 'اختر مهارة أولًا.',
            'newSkillId.exists' => 'المهارة المحددة غير موجودة.',
            'newProficiency.in' => 'المستوى المحدد غير صالح.',
            'newYears.integer' => 'سنوات الخبرة يجب أن تكون رقمًا.',
            'newYears.min' => 'سنوات الخبرة لا يمكن أن تكون سالبة.',
            'newYears.max' => 'سنوات الخبرة يجب ألا تتجاوز 60.',
            'proficiencies.*.in' => 'المستوى المحدد غير صالح.',
            'years.*.integer' => 'سنوات الخبرة يجب أن تكون رقمًا.',
            'years.*.min' => 'سنوات الخبرة لا يمكن أن تكون سالبة.',
            'years.*.max' => 'سنوات الخبرة يجب ألا تتجاوز 60.',
            'resumeFile.required' => 'اختر ملف السيرة الذاتية.',
            'resumeFile.mimes' => 'يجب أن يكون الملف بصيغة PDF أو DOC أو DOCX.',
            'resumeFile.max' => 'حجم الملف يجب ألا يتجاوز 5 ميغابايت.',
        ];
    }
}; ?>

<div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">ملفي الشخصي</h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    أكمل بياناتك ومهاراتك ليزيد ظهورك أمام أصحاب العمل.
                </p>
            </div>

            <flux:badge size="sm">
                {{ auth()->user()->role === 'employer' ? 'صاحب عمل' : 'باحث عن عمل' }}
            </flux:badge>
        </div>

        <form wire:submit="updateProfile" class="space-y-5 rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-neutral-900 dark:text-white">المعلومات الأساسية</h2>

            <flux:input wire:model="name" label="الاسم الكامل" type="text" required autocomplete="name" />

            <flux:textarea
                wire:model="profile"
                label="نبذة عنك"
                rows="4"
                placeholder="اكتب نبذة قصيرة عن خبرتك وأهدافك المهنية..."
            />

            <flux:input
                wire:model="location"
                label="الموقع"
                type="text"
                placeholder="مثال: الرياض، السعودية"
                autocomplete="address-level2"
            />

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary">حفظ التغييرات</flux:button>
            </div>
        </form>

        <section class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-base font-semibold text-neutral-900 dark:text-white">المهارات</h2>
                <span class="text-xs text-neutral-500 dark:text-neutral-400">
                    {{ $this->userSkills->count() }} مهارة
                </span>
            </div>

            @if ($this->availableSkills->isNotEmpty())
                <form wire:submit="addSkill" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-6 sm:items-end">
                    <div class="sm:col-span-3">
                        <flux:select wire:model="newSkillId" label="المهارة" placeholder="اختر مهارة">
                            @foreach ($this->availableSkills as $skill)
                                <flux:select.option value="{{ $skill->id }}">{{ $skill->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div class="sm:col-span-2">
                        <flux:select wire:model="newProficiency" label="المستوى" placeholder="بدون تحديد">
                            <flux:select.option value="beginner">مبتدئ</flux:select.option>
                            <flux:select.option value="intermediate">متوسط</flux:select.option>
                            <flux:select.option value="advanced">متقدم</flux:select.option>
                            <flux:select.option value="expert">خبير</flux:select.option>
                        </flux:select>
                    </div>

                    <div>
                        <flux:input wire:model="newYears" label="سنوات الخبرة" type="number" min="0" max="60" />
                    </div>

                    <div class="flex justify-end sm:col-span-6">
                        <flux:button type="submit" size="sm">إضافة المهارة</flux:button>
                    </div>
                </form>
            @else
                <p class="mt-3 text-sm text-neutral-500 dark:text-neutral-400">
                    تمت إضافة جميع المهارات المتاحة.
                </p>
            @endif

            <ul class="mt-5 divide-y divide-neutral-200 dark:divide-neutral-700">
                @forelse ($this->userSkills as $skill)
                    <li class="grid grid-cols-1 gap-3 py-3 sm:grid-cols-12 sm:items-center" wire:key="skill-{{ $skill->id }}">
                        <div class="sm:col-span-4">
                            <p class="font-medium text-neutral-900 dark:text-white">{{ $skill->name }}</p>
                        </div>

                        <div class="sm:col-span-4">
                            <flux:select wire:model="proficiencies.{{ $skill->id }}" size="sm" aria-label="مستوى {{ $skill->name }}">
                                <flux:select.option value="">بدون تحديد</flux:select.option>
                                <flux:select.option value="beginner">مبتدئ</flux:select.option>
                                <flux:select.option value="intermediate">متوسط</flux:select.option>
                                <flux:select.option value="advanced">متقدم</flux:select.option>
                                <flux:select.option value="expert">خبير</flux:select.option>
                            </flux:select>
                        </div>

                        <div class="sm:col-span-2">
                            <flux:input
                                wire:model="years.{{ $skill->id }}"
                                type="number"
                                min="0"
                                max="60"
                                size="sm"
                                placeholder="سنوات"
                                aria-label="سنوات الخبرة في {{ $skill->name }}"
                            />
                        </div>

                        <div class="flex gap-2 sm:col-span-2 sm:justify-end">
                            <flux:button size="sm" wire:click="updateSkill({{ $skill->id }})">حفظ</flux:button>
                            <flux:button
                                size="sm"
                                variant="danger"
                                wire:click="removeSkill({{ $skill->id }})"
                                wire:confirm="هل تريد إزالة هذه المهارة؟"
                            >
                                إزالة
                            </flux:button>
                        </div>
                    </li>
                @empty
                    <li class="py-4 text-sm text-neutral-500 dark:text-neutral-400">
                        لم تضف أي مهارات بعد.
                    </li>
                @endforelse
            </ul>
        </section>

        <section class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-neutral-900 dark:text-white">السيرة الذاتية</h2>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                ارفع ملف PDF أو Word (بحد أقصى 5 ميغابايت) وحدد سيرة رئيسية للتقديم.
            </p>

            <form wire:submit="uploadResume" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <label for="resume-file" class="mb-1 block text-sm font-medium text-neutral-900 dark:text-neutral-100">
                        ملف السيرة الذاتية
                    </label>

                    <input
                        id="resume-file"
                        type="file"
                        wire:model="resumeFile"
                        accept=".pdf,.doc,.docx"
                        class="block w-full cursor-pointer rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-700 file:me-3 file:rounded-md file:border-0 file:bg-neutral-100 file:px-3 file:py-1.5 file:text-sm file:font-medium dark:border-neutral-600 dark:bg-zinc-900 dark:text-neutral-200 dark:file:bg-neutral-700"
                    />

                    @error('resumeFile')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    <p wire:loading wire:target="resumeFile" class="mt-1 text-xs text-neutral-500">
                        جارٍ رفع الملف...
                    </p>
                </div>

                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="uploadResume">
                    رفع السيرة
                </flux:button>
            </form>

            <ul class="mt-5 divide-y divide-neutral-200 dark:divide-neutral-700">
                @forelse ($this->userResumes as $resume)
                    <li class="flex flex-wrap items-center gap-3 py-3" wire:key="resume-{{ $resume->id }}">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="truncate font-medium text-neutral-900 dark:text-white">{{ $resume->file_name }}</span>

                                @if ($resume->is_primary)
                                    <flux:badge size="sm" color="green">الرئيسية</flux:badge>
                                @endif
                            </div>

                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                {{ $resume->created_at?->format('Y-m-d') }}
                                @if ($resume->file_size)
                                    · {{ number_format($resume->file_size / 1024) }} كيلوبايت
                                @endif
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <flux:link href="{{ $resume->downloadUrl() }}" target="_blank" rel="noopener" class="text-sm">
                                تحميل
                            </flux:link>

                            @unless ($resume->is_primary)
                                <flux:button size="sm" wire:click="setPrimaryResume({{ $resume->id }})">
                                    تعيين كرئيسية
                                </flux:button>
                            @endunless

                            <flux:button
                                size="sm"
                                variant="danger"
                                wire:click="deleteResume({{ $resume->id }})"
                                wire:confirm="هل تريد حذف هذه السيرة الذاتية؟"
                            >
                                حذف
                            </flux:button>
                        </div>
                    </li>
                @empty
                    <li class="py-4 text-sm text-neutral-500 dark:text-neutral-400">
                        لم ترفع أي سيرة ذاتية بعد.
                    </li>
                @endforelse
            </ul>
        </section>
    </div>

