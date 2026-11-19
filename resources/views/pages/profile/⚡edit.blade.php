<?php

use App\Models\Education;
use App\Models\Skill;
use App\Models\WorkExperience;
use App\Support\SeekerProfileCompleteness;
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

    public ?int $editingExperienceId = null;

    public string $experienceTitle = '';

    public string $experienceCompany = '';

    public string $experienceLocation = '';

    public string $experienceStartedAt = '';

    public string $experienceEndedAt = '';

    public bool $experienceIsCurrent = false;

    public string $experienceDescription = '';

    public ?int $editingEducationId = null;

    public string $educationDegree = '';

    public string $educationInstitution = '';

    public string $educationField = '';

    public string $educationStartedAt = '';

    public string $educationEndedAt = '';

    public bool $educationIsCurrent = false;

    public string $educationDescription = '';

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

        unset($this->completeness);

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
        unset($this->userSkills, $this->availableSkills, $this->completeness);

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
        unset($this->userSkills, $this->availableSkills, $this->completeness);

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
        unset($this->userResumes, $this->completeness);

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

        unset($this->userResumes, $this->completeness);

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

        unset($this->userResumes, $this->completeness);

        Flux::toast(variant: 'success', text: 'تم حذف السيرة الذاتية.');
    }

    public function saveExperience(): void
    {
        $validated = $this->validate([
            'experienceTitle' => ['required', 'string', 'max:150'],
            'experienceCompany' => ['required', 'string', 'max:150'],
            'experienceLocation' => ['nullable', 'string', 'max:100'],
            'experienceStartedAt' => ['required', 'date', 'before_or_equal:today'],
            'experienceEndedAt' => ['nullable', 'date', 'after_or_equal:experienceStartedAt'],
            'experienceIsCurrent' => ['boolean'],
            'experienceDescription' => ['nullable', 'string', 'max:2000'],
        ], $this->messages(), [
            'experienceTitle' => 'المسمى الوظيفي',
            'experienceCompany' => 'جهة العمل',
            'experienceLocation' => 'مكان العمل',
            'experienceStartedAt' => 'تاريخ البداية',
            'experienceEndedAt' => 'تاريخ النهاية',
            'experienceDescription' => 'الوصف',
        ]);

        $attributes = [
            'job_title' => $validated['experienceTitle'],
            'company' => $validated['experienceCompany'],
            'location' => filled($validated['experienceLocation'] ?? null) ? $validated['experienceLocation'] : null,
            'started_at' => $validated['experienceStartedAt'],
            'ended_at' => $validated['experienceIsCurrent'] ? null : ($validated['experienceEndedAt'] ?? null),
            'is_current' => $validated['experienceIsCurrent'],
            'description' => filled($validated['experienceDescription'] ?? null) ? $validated['experienceDescription'] : null,
        ];

        $user = Auth::user();

        if ($this->editingExperienceId !== null) {
            $user->workExperiences()->findOrFail($this->editingExperienceId)->update($attributes);

            Flux::toast(variant: 'success', text: 'تم تحديث الخبرة العملية.');
        } else {
            $user->workExperiences()->create($attributes);

            Flux::toast(variant: 'success', text: 'تمت إضافة الخبرة العملية.');
        }

        $this->resetExperienceForm();
        unset($this->workExperiences, $this->completeness);
    }

    public function editExperience(int $experienceId): void
    {
        $experience = Auth::user()->workExperiences()->findOrFail($experienceId);

        $this->editingExperienceId = (int) $experience->id;
        $this->experienceTitle = $experience->job_title;
        $this->experienceCompany = $experience->company;
        $this->experienceLocation = $experience->location ?? '';
        $this->experienceStartedAt = $experience->started_at?->format('Y-m-d') ?? '';
        $this->experienceEndedAt = $experience->ended_at?->format('Y-m-d') ?? '';
        $this->experienceIsCurrent = $experience->is_current;
        $this->experienceDescription = $experience->description ?? '';
    }

    public function cancelExperienceEdit(): void
    {
        $this->resetExperienceForm();
    }

    public function deleteExperience(int $experienceId): void
    {
        Auth::user()->workExperiences()->findOrFail($experienceId)->delete();

        if ($this->editingExperienceId === $experienceId) {
            $this->resetExperienceForm();
        }

        unset($this->workExperiences, $this->completeness);

        Flux::toast(variant: 'success', text: 'تم حذف الخبرة العملية.');
    }

    public function saveEducation(): void
    {
        $validated = $this->validate([
            'educationDegree' => ['required', 'string', 'max:150'],
            'educationInstitution' => ['required', 'string', 'max:150'],
            'educationField' => ['nullable', 'string', 'max:150'],
            'educationStartedAt' => ['required', 'date', 'before_or_equal:today'],
            'educationEndedAt' => ['nullable', 'date', 'after_or_equal:educationStartedAt'],
            'educationIsCurrent' => ['boolean'],
            'educationDescription' => ['nullable', 'string', 'max:2000'],
        ], $this->messages(), [
            'educationDegree' => 'الدرجة العلمية',
            'educationInstitution' => 'الجهة التعليمية',
            'educationField' => 'التخصص',
            'educationStartedAt' => 'تاريخ البداية',
            'educationEndedAt' => 'تاريخ النهاية',
            'educationDescription' => 'الوصف',
        ]);

        $attributes = [
            'degree' => $validated['educationDegree'],
            'institution' => $validated['educationInstitution'],
            'field_of_study' => filled($validated['educationField'] ?? null) ? $validated['educationField'] : null,
            'started_at' => $validated['educationStartedAt'],
            'ended_at' => $validated['educationIsCurrent'] ? null : ($validated['educationEndedAt'] ?? null),
            'is_current' => $validated['educationIsCurrent'],
            'description' => filled($validated['educationDescription'] ?? null) ? $validated['educationDescription'] : null,
        ];

        $user = Auth::user();

        if ($this->editingEducationId !== null) {
            $user->educations()->findOrFail($this->editingEducationId)->update($attributes);

            Flux::toast(variant: 'success', text: 'تم تحديث المؤهل التعليمي.');
        } else {
            $user->educations()->create($attributes);

            Flux::toast(variant: 'success', text: 'تمت إضافة المؤهل التعليمي.');
        }

        $this->resetEducationForm();
        unset($this->educations, $this->completeness);
    }

    public function editEducation(int $educationId): void
    {
        $education = Auth::user()->educations()->findOrFail($educationId);

        $this->editingEducationId = (int) $education->id;
        $this->educationDegree = $education->degree;
        $this->educationInstitution = $education->institution;
        $this->educationField = $education->field_of_study ?? '';
        $this->educationStartedAt = $education->started_at?->format('Y-m-d') ?? '';
        $this->educationEndedAt = $education->ended_at?->format('Y-m-d') ?? '';
        $this->educationIsCurrent = $education->is_current;
        $this->educationDescription = $education->description ?? '';
    }

    public function cancelEducationEdit(): void
    {
        $this->resetEducationForm();
    }

    public function deleteEducation(int $educationId): void
    {
        Auth::user()->educations()->findOrFail($educationId)->delete();

        if ($this->editingEducationId === $educationId) {
            $this->resetEducationForm();
        }

        unset($this->educations, $this->completeness);

        Flux::toast(variant: 'success', text: 'تم حذف المؤهل التعليمي.');
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

    /**
     * @return Collection<int, WorkExperience>
     */
    #[Computed]
    public function workExperiences(): Collection
    {
        return Auth::user()->workExperiences()->get();
    }

    /**
     * @return Collection<int, Education>
     */
    #[Computed]
    public function educations(): Collection
    {
        return Auth::user()->educations()->get();
    }

    #[Computed]
    public function completeness(): SeekerProfileCompleteness
    {
        return SeekerProfileCompleteness::for(Auth::user());
    }

    private function resetExperienceForm(): void
    {
        $this->reset([
            'editingExperienceId',
            'experienceTitle',
            'experienceCompany',
            'experienceLocation',
            'experienceStartedAt',
            'experienceEndedAt',
            'experienceIsCurrent',
            'experienceDescription',
        ]);
    }

    private function resetEducationForm(): void
    {
        $this->reset([
            'editingEducationId',
            'educationDegree',
            'educationInstitution',
            'educationField',
            'educationStartedAt',
            'educationEndedAt',
            'educationIsCurrent',
            'educationDescription',
        ]);
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
            'experienceTitle.required' => 'المسمى الوظيفي مطلوب.',
            'experienceTitle.max' => 'المسمى الوظيفي يجب ألا يتجاوز 150 حرفًا.',
            'experienceCompany.required' => 'جهة العمل مطلوبة.',
            'experienceCompany.max' => 'جهة العمل يجب ألا تتجاوز 150 حرفًا.',
            'experienceLocation.max' => 'مكان العمل يجب ألا يتجاوز 100 حرف.',
            'experienceStartedAt.required' => 'تاريخ البداية مطلوب.',
            'experienceStartedAt.date' => 'تاريخ البداية غير صالح.',
            'experienceStartedAt.before_or_equal' => 'تاريخ البداية يجب ألا يكون في المستقبل.',
            'experienceEndedAt.date' => 'تاريخ النهاية غير صالح.',
            'experienceEndedAt.after_or_equal' => 'تاريخ النهاية يجب ألا يسبق تاريخ البداية.',
            'experienceDescription.max' => 'الوصف يجب ألا يتجاوز 2000 حرف.',
            'educationDegree.required' => 'الدرجة العلمية مطلوبة.',
            'educationDegree.max' => 'الدرجة العلمية يجب ألا تتجاوز 150 حرفًا.',
            'educationInstitution.required' => 'الجهة التعليمية مطلوبة.',
            'educationInstitution.max' => 'الجهة التعليمية يجب ألا تتجاوز 150 حرفًا.',
            'educationField.max' => 'التخصص يجب ألا يتجاوز 150 حرفًا.',
            'educationStartedAt.required' => 'تاريخ البداية مطلوب.',
            'educationStartedAt.date' => 'تاريخ البداية غير صالح.',
            'educationStartedAt.before_or_equal' => 'تاريخ البداية يجب ألا يكون في المستقبل.',
            'educationEndedAt.date' => 'تاريخ النهاية غير صالح.',
            'educationEndedAt.after_or_equal' => 'تاريخ النهاية يجب ألا يسبق تاريخ البداية.',
            'educationDescription.max' => 'الوصف يجب ألا يتجاوز 2000 حرف.',
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

        @if (auth()->user()->isSeeker())
            <section class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-base font-semibold text-neutral-900 dark:text-white">اكتمال الملف الشخصي</h2>
                    <span class="text-sm font-semibold text-neutral-900 dark:text-white">
                        {{ $this->completeness->percentage() }}%
                    </span>
                </div>

                <flux:progress class="mt-3" color="blue" :value="$this->completeness->percentage()" />

                <ul class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach ($this->completeness->items() as $item)
                        <li class="flex items-center gap-2 text-sm">
                            <flux:icon
                                :name="$item['complete'] ? 'check-circle' : 'minus-circle'"
                                variant="micro"
                                class="{{ $item['complete'] ? 'text-green-600 dark:text-green-400' : 'text-neutral-400' }}"
                            />

                            <span class="{{ $item['complete'] ? 'text-neutral-700 dark:text-neutral-200' : 'text-neutral-500 dark:text-neutral-400' }}">
                                {{ $item['label'] }}
                            </span>

                            @unless ($item['required'])
                                <flux:badge size="sm">اختياري</flux:badge>
                            @endunless
                        </li>
                    @endforeach
                </ul>

                @if (! $this->completeness->canApply())
                    <p class="mt-4 rounded-lg bg-amber-50 p-3 text-sm text-amber-800 dark:bg-amber-400/10 dark:text-amber-200">
                        لتتمكن من إرسال طلباتك، أكمل: {{ implode('، ', $this->completeness->missingForApplying()) }}.
                    </p>
                @else
                    <p class="mt-4 text-sm text-green-700 dark:text-green-400">
                        ملفك مكتمل بما يكفي للتقديم على الوظائف.
                    </p>
                @endif
            </section>
        @endif

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
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-base font-semibold text-neutral-900 dark:text-white">الخبرة العملية</h2>
                <span class="text-xs text-neutral-500 dark:text-neutral-400">
                    {{ $this->workExperiences->count() }} خبرة
                </span>
            </div>

            <form wire:submit="saveExperience" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <flux:input wire:model="experienceTitle" label="المسمى الوظيفي" required />
                <flux:input wire:model="experienceCompany" label="جهة العمل" required />
                <flux:input wire:model="experienceLocation" label="مكان العمل" placeholder="مثال: الرياض، السعودية" />
                <flux:input wire:model="experienceStartedAt" label="تاريخ البداية" type="date" required />
                <flux:input
                    wire:model="experienceEndedAt"
                    label="تاريخ النهاية"
                    type="date"
                    :disabled="$experienceIsCurrent"
                />

                <div class="flex items-end sm:col-span-2">
                    <flux:checkbox wire:model.live="experienceIsCurrent" label="ما زلت أعمل هنا" />
                </div>

                <div class="sm:col-span-2">
                    <flux:textarea
                        wire:model="experienceDescription"
                        label="وصف المهام (اختياري)"
                        rows="3"
                        placeholder="اذكر أبرز المهام والإنجازات..."
                    />
                </div>

                <div class="flex flex-wrap justify-end gap-2 sm:col-span-2">
                    @if ($editingExperienceId)
                        <flux:button type="button" wire:click="cancelExperienceEdit">إلغاء</flux:button>
                    @endif

                    <flux:button type="submit" variant="primary">
                        {{ $editingExperienceId ? 'حفظ التعديل' : 'إضافة الخبرة' }}
                    </flux:button>
                </div>
            </form>

            <ul class="mt-5 divide-y divide-neutral-200 dark:divide-neutral-700">
                @forelse ($this->workExperiences as $experience)
                    <li class="py-4" wire:key="experience-{{ $experience->id }}">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-medium text-neutral-900 dark:text-white">{{ $experience->job_title }}</p>

                                    @if ($experience->is_current)
                                        <flux:badge size="sm" color="green">حالية</flux:badge>
                                    @endif
                                </div>

                                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-300">
                                    {{ $experience->company }}
                                    @if ($experience->location)
                                        · {{ $experience->location }}
                                    @endif
                                </p>

                                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">{{ $experience->period() }}</p>

                                @if ($experience->description)
                                    <p class="mt-2 whitespace-pre-line text-sm text-neutral-600 dark:text-neutral-300">
                                        {{ $experience->description }}
                                    </p>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <flux:button size="sm" icon="pencil" wire:click="editExperience({{ $experience->id }})">
                                    تعديل
                                </flux:button>

                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    icon="trash"
                                    wire:click="deleteExperience({{ $experience->id }})"
                                    wire:confirm="هل تريد حذف هذه الخبرة؟"
                                >
                                    حذف
                                </flux:button>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="py-4 text-sm text-neutral-500 dark:text-neutral-400">
                        لم تضف أي خبرة عملية بعد.
                    </li>
                @endforelse
            </ul>
        </section>

        <section class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-base font-semibold text-neutral-900 dark:text-white">المؤهلات التعليمية</h2>
                <span class="text-xs text-neutral-500 dark:text-neutral-400">
                    {{ $this->educations->count() }} مؤهل
                </span>
            </div>

            <form wire:submit="saveEducation" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <flux:input wire:model="educationDegree" label="الدرجة العلمية" placeholder="مثال: بكالوريوس" required />
                <flux:input wire:model="educationInstitution" label="الجهة التعليمية" required />
                <flux:input wire:model="educationField" label="التخصص" />
                <flux:input wire:model="educationStartedAt" label="تاريخ البداية" type="date" required />
                <flux:input
                    wire:model="educationEndedAt"
                    label="تاريخ النهاية"
                    type="date"
                    :disabled="$educationIsCurrent"
                />

                <div class="flex items-end sm:col-span-2">
                    <flux:checkbox wire:model.live="educationIsCurrent" label="ما زلت أدرس هنا" />
                </div>

                <div class="sm:col-span-2">
                    <flux:textarea
                        wire:model="educationDescription"
                        label="ملاحظات (اختياري)"
                        rows="2"
                        placeholder="مثال: مشروع التخرج أو المعدل التراكمي..."
                    />
                </div>

                <div class="flex flex-wrap justify-end gap-2 sm:col-span-2">
                    @if ($editingEducationId)
                        <flux:button type="button" wire:click="cancelEducationEdit">إلغاء</flux:button>
                    @endif

                    <flux:button type="submit" variant="primary">
                        {{ $editingEducationId ? 'حفظ التعديل' : 'إضافة المؤهل' }}
                    </flux:button>
                </div>
            </form>

            <ul class="mt-5 divide-y divide-neutral-200 dark:divide-neutral-700">
                @forelse ($this->educations as $education)
                    <li class="py-4" wire:key="education-{{ $education->id }}">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-medium text-neutral-900 dark:text-white">
                                        {{ $education->degree }}
                                        @if ($education->field_of_study)
                                            · {{ $education->field_of_study }}
                                        @endif
                                    </p>

                                    @if ($education->is_current)
                                        <flux:badge size="sm" color="green">جاري</flux:badge>
                                    @endif
                                </div>

                                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-300">{{ $education->institution }}</p>
                                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">{{ $education->period() }}</p>

                                @if ($education->description)
                                    <p class="mt-2 whitespace-pre-line text-sm text-neutral-600 dark:text-neutral-300">
                                        {{ $education->description }}
                                    </p>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <flux:button size="sm" icon="pencil" wire:click="editEducation({{ $education->id }})">
                                    تعديل
                                </flux:button>

                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    icon="trash"
                                    wire:click="deleteEducation({{ $education->id }})"
                                    wire:confirm="هل تريد حذف هذا المؤهل؟"
                                >
                                    حذف
                                </flux:button>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="py-4 text-sm text-neutral-500 dark:text-neutral-400">
                        لم تضف أي مؤهل تعليمي بعد.
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

