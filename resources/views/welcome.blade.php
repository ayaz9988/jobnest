@php
    $title = 'وظائف عربية موثوقة';
    $featured = $jobs->first();
    $isAuthed = auth()->check();
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark scroll-smooth">
    <head>
        @include('partials.head')
        <meta name="description" content="JobNest منصة عربية للوظائف: تصفّح وظائف الشركات، احفظ ما يناسبك، وقدّم بسيرتك الذاتية في دقيقة." />
    </head>
    <body class="min-h-screen bg-white font-sans text-neutral-900 antialiased dark:bg-zinc-950 dark:text-white">
        <header class="sticky top-0 z-30 border-b border-neutral-200/70 bg-white/85 backdrop-blur-md dark:border-white/10 dark:bg-zinc-950/80">
            <div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
                <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white ring-1 ring-black/5 dark:ring-white/10">
                        <img src="{{ asset('images/jobnest-mark.png') }}" alt="JobNest" class="size-9 object-contain" />
                    </span>

                    <span class="hidden min-w-0 sm:block">
                        <span class="block truncate text-sm font-bold tracking-tight">JobNest</span>
                        <span class="block truncate text-[11px] font-medium text-neutral-500 dark:text-neutral-400">وظائف عربية موثوقة</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-7 text-sm font-medium text-neutral-600 dark:text-neutral-300 md:flex">
                    <a href="#jobs" class="transition hover:text-violet-700 dark:hover:text-violet-300">الوظائف</a>
                    <a href="#how" class="transition hover:text-violet-700 dark:hover:text-violet-300">كيف يعمل</a>
                    <a href="#categories" class="transition hover:text-violet-700 dark:hover:text-violet-300">التصنيفات</a>
                </nav>

                <div class="flex items-center gap-2">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700"
                        >
                            لوحة التحكم
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="rounded-xl px-3 py-2 text-sm font-semibold text-neutral-600 transition hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-white/10"
                        >
                            تسجيل الدخول
                        </a>
                        <a
                            href="{{ route('register') }}"
                            class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700"
                        >
                            إنشاء حساب
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <main>
            <section class="relative overflow-hidden bg-gradient-to-bl from-violet-600 via-violet-700 to-indigo-950 text-white">
                <div class="pointer-events-none absolute -top-24 -start-16 size-72 rounded-full bg-white/10 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-32 -end-16 size-96 rounded-full bg-violet-300/20 blur-3xl"></div>

                <div class="relative mx-auto grid w-full max-w-6xl gap-12 px-4 py-16 sm:px-6 sm:py-24 lg:grid-cols-[1.15fr_0.85fr] lg:items-center">
                    <div class="max-w-2xl">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-violet-50 ring-1 ring-white/20">
                            <flux:icon.sparkles class="size-3.5" />
                            منصة عربية للوظائف
                        </span>

                        <h1 class="mt-5 text-4xl font-bold leading-[1.25] sm:text-5xl">
                            اعثر على وظيفتك القادمة في أفضل الشركات العربية
                        </h1>

                        <p class="mt-5 max-w-xl text-base leading-8 text-violet-50/90">
                            تصفّح الفرص في مختلف المجالات، احفظ ما يناسبك منها، وقدّم بملفك وسيرتك الذاتية في دقائق — ومتابعة كل طلب من مكان واحد.
                        </p>

                        <div class="mt-8 flex flex-wrap gap-3">
                            <a
                                href="{{ $isAuthed ? route('jobs.index') : route('register') }}"
                                class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-bold text-violet-700 shadow-lg shadow-indigo-950/30 transition hover:bg-violet-50"
                            >
                                {{ $isAuthed ? 'تصفح الوظائف' : 'ابدأ الآن مجاناً' }}
                                <flux:icon.arrow-left class="size-4" />
                            </a>

                            <a
                                href="{{ $isAuthed ? route('dashboard') : route('login') }}"
                                class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/15"
                            >
                                {{ $isAuthed ? 'لوحة التحكم' : 'لديّ حساب' }}
                            </a>
                        </div>

                        <dl class="mt-12 grid max-w-md grid-cols-3 gap-4 border-t border-white/15 pt-6">
                            <div>
                                <dt class="sr-only">وظائف متاحة</dt>
                                <dd class="text-2xl font-bold">{{ number_format($stats['jobs']) }}</dd>
                                <p class="mt-1 text-xs text-violet-50/80">وظيفة متاحة</p>
                            </div>
                            <div>
                                <dt class="sr-only">شركات</dt>
                                <dd class="text-2xl font-bold">{{ number_format($stats['companies']) }}</dd>
                                <p class="mt-1 text-xs text-violet-50/80">شركة مسجّلة</p>
                            </div>
                            <div>
                                <dt class="sr-only">تصنيفات</dt>
                                <dd class="text-2xl font-bold">{{ number_format($stats['categories']) }}</dd>
                                <p class="mt-1 text-xs text-violet-50/80">تصنيفاً وظيفياً</p>
                            </div>
                        </dl>
                    </div>

                    @if ($featured)
                        <div class="relative hidden lg:block">
                            <div class="absolute inset-0 -rotate-3 rounded-3xl bg-white/10 ring-1 ring-white/20"></div>

                            <a
                                href="{{ route('jobs.show', $featured) }}"
                                class="relative block rounded-3xl bg-white p-6 text-neutral-900 shadow-2xl shadow-indigo-950/40 transition hover:-translate-y-1 dark:bg-zinc-900 dark:text-white"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-violet-50 px-2.5 py-1 text-[11px] font-semibold text-violet-700 dark:bg-violet-500/10 dark:text-violet-300">
                                        <flux:icon.sparkles class="size-3" />
                                        وظيفة مميزة
                                    </span>

                                    <span class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                        {{ $featured->published_at?->locale('ar')->diffForHumans() }}
                                    </span>
                                </div>

                                <div class="mt-5 flex items-center gap-3">
                                    <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-violet-50 text-base font-semibold text-violet-700 dark:bg-violet-500/10 dark:text-violet-300">
                                        {{ $featured->postedBy?->initials() ?? '؟' }}
                                    </span>

                                    <div class="min-w-0">
                                        <p class="text-lg font-bold leading-snug">{{ $featured->title }}</p>
                                        <p class="mt-0.5 truncate text-xs text-neutral-500 dark:text-neutral-400">
                                            {{ $featured->postedBy?->name }}
                                            @if ($featured->location)
                                                · {{ $featured->location }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span class="rounded-full bg-violet-50 px-2.5 py-1 text-[11px] font-medium text-violet-700 dark:bg-violet-500/10 dark:text-violet-300">
                                        {{ \App\Support\JobListingOptions::workplaceType($featured->workplace_type) }}
                                    </span>
                                    <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-[11px] font-medium text-neutral-600 dark:bg-zinc-800 dark:text-neutral-300">
                                        {{ \App\Support\JobListingOptions::employmentType($featured->employment_type) }}
                                    </span>
                                </div>

                                <div class="mt-5 flex items-center justify-between gap-3 border-t border-neutral-100 pt-4 dark:border-zinc-800">
                                    @if ($featured->salary_min || $featured->salary_max)
                                        <span class="flex items-center gap-1.5 text-sm font-semibold" dir="ltr">
                                            <flux:icon.banknotes class="size-4 text-neutral-400" />
                                            {{ $featured->salary_min ? number_format($featured->salary_min) : '—' }}
                                            -
                                            {{ $featured->salary_max ? number_format($featured->salary_max) : '—' }}
                                            <span class="text-xs font-normal text-neutral-500">{{ $featured->salary_currency }}</span>
                                        </span>
                                    @else
                                        <span class="text-xs text-neutral-500">الراتب غير محدد</span>
                                    @endif

                                    <span class="inline-flex items-center gap-1 text-sm font-semibold text-violet-700 dark:text-violet-400">
                                        عرض التفاصيل
                                        <flux:icon.arrow-left class="size-4" />
                                    </span>
                                </div>
                            </a>
                        </div>
                    @endif
                </div>
            </section>

            <section id="jobs" class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold text-violet-600 dark:text-violet-400">فرص جديدة</p>
                        <h2 class="mt-1 text-2xl font-bold sm:text-3xl">أحدث الوظائف</h2>
                    </div>

                    <a
                        href="{{ route('jobs.index') }}"
                        class="inline-flex items-center gap-1 text-sm font-semibold text-violet-700 transition hover:text-violet-800 dark:text-violet-400 dark:hover:text-violet-300"
                    >
                        عرض جميع الوظائف
                        <flux:icon.arrow-left class="size-4" />
                    </a>
                </div>

                <div class="mt-8 grid auto-rows-fr gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($jobs as $job)
                        <article class="flex h-full flex-col rounded-2xl border border-neutral-200 bg-white p-5 transition hover:border-violet-300 hover:shadow-lg hover:shadow-violet-900/5 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-violet-700/60">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-sm font-semibold text-violet-700 dark:bg-violet-500/10 dark:text-violet-300">
                                        {{ $job->postedBy?->initials() ?? '؟' }}
                                    </span>

                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-medium text-neutral-600 dark:text-neutral-300">{{ $job->postedBy?->name ?? 'شركة غير محددة' }}</p>
                                        <p class="mt-0.5 text-[11px] text-neutral-400 dark:text-neutral-500">{{ $job->published_at?->locale('ar')->diffForHumans() }}</p>
                                    </div>
                                </div>

                                <span class="shrink-0 rounded-full bg-violet-50 px-2.5 py-1 text-[11px] font-medium text-violet-700 dark:bg-violet-500/10 dark:text-violet-300">
                                    {{ \App\Support\JobListingOptions::workplaceType($job->workplace_type) }}
                                </span>
                            </div>

                            <a
                                href="{{ route('jobs.show', $job) }}"
                                class="mt-4 text-lg font-semibold leading-snug transition hover:text-violet-700 dark:hover:text-violet-300"
                            >
                                {{ $job->title }}
                            </a>

                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                @if ($job->category)
                                    {{ $job->category->name }}
                                    ·
                                @endif
                                {{ $job->location ?: 'غير محدد' }}
                            </p>

                            @if ($job->skills->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    @foreach ($job->skills->take(3) as $skill)
                                        <span class="rounded-md bg-zinc-100 px-2 py-0.5 text-[11px] font-medium text-neutral-600 dark:bg-zinc-800 dark:text-neutral-300">
                                            {{ $skill->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-auto flex items-center justify-between gap-2 pt-5">
                                @if ($job->salary_min || $job->salary_max)
                                    <span class="flex items-center gap-1.5 text-sm font-semibold" dir="ltr">
                                        <flux:icon.banknotes class="size-4 text-neutral-400" />
                                        {{ $job->salary_min ? number_format($job->salary_min) : '—' }}
                                        -
                                        {{ $job->salary_max ? number_format($job->salary_max) : '—' }}
                                        <span class="text-xs font-normal text-neutral-500">{{ $job->salary_currency }}</span>
                                    </span>
                                @else
                                    <span class="text-xs text-neutral-500">الراتب غير محدد</span>
                                @endif

                                <a
                                    href="{{ route('jobs.show', $job) }}"
                                    class="inline-flex items-center gap-1 text-sm font-semibold text-violet-700 transition hover:text-violet-800 dark:text-violet-400 dark:hover:text-violet-300"
                                >
                                    التفاصيل
                                    <flux:icon.arrow-left class="size-4" />
                                </a>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-neutral-300 p-10 text-center sm:col-span-2 lg:col-span-3 dark:border-zinc-700">
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">لا توجد وظائف منشورة حالياً، عاود الزيارة قريباً.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section id="how" class="border-y border-neutral-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900/40">
                <div class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6">
                    <div class="max-w-2xl">
                        <p class="text-xs font-semibold text-violet-600 dark:text-violet-400">كيف يعمل</p>
                        <h2 class="mt-1 text-2xl font-bold sm:text-3xl">ثلاث خطوات تفصلك عن وظيفتك</h2>
                        <p class="mt-3 text-sm leading-7 text-neutral-500 dark:text-neutral-400">
                            من إنشاء الحساب حتى متابعة الطلب، كل شيء في مكان واحد وبتجربة عربية بالكامل.
                        </p>
                    </div>

                    <div class="mt-10 grid gap-4 md:grid-cols-3">
                        @foreach ([
                            ['icon' => 'user', 'title' => 'أنشئ حسابك', 'text' => 'سجّل مجاناً كباحث عن عمل في أقل من دقيقة.'],
                            ['icon' => 'document-text', 'title' => 'أكمل ملفك الشخصي', 'text' => 'أضف مهاراتك وخبراتك وارفع سيرتك الذاتية.'],
                            ['icon' => 'paper-airplane', 'title' => 'قدّم وتابع', 'text' => 'تقدّم على الوظائف وتابع حالتها والإشعارات أولاً بأول.'],
                        ] as $index => $step)
                            <div class="relative rounded-2xl border border-neutral-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                                <span class="flex size-8 items-center justify-center rounded-lg bg-violet-50 text-sm font-bold text-violet-700 dark:bg-violet-500/10 dark:text-violet-300">
                                    {{ $index + 1 }}
                                </span>

                                <flux:icon :name="$step['icon']" class="mt-5 size-6 text-violet-600 dark:text-violet-400" />

                                <h3 class="mt-3 font-semibold">{{ $step['title'] }}</h3>
                                <p class="mt-1.5 text-sm leading-6 text-neutral-500 dark:text-neutral-400">{{ $step['text'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="categories" class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold text-violet-600 dark:text-violet-400">التصنيفات</p>
                    <h2 class="mt-1 text-2xl font-bold sm:text-3xl">استكشف الوظائف حسب المجال</h2>
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($categories as $category)
                        <a
                            href="{{ route('jobs.index') }}"
                            class="group rounded-2xl border border-neutral-200 bg-white p-5 transition hover:border-violet-300 hover:shadow-lg hover:shadow-violet-900/5 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-violet-700/60"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="font-semibold transition group-hover:text-violet-700 dark:group-hover:text-violet-300">{{ $category->name }}</h3>
                                <flux:icon.arrow-left class="size-4 text-violet-600 transition group-hover:-translate-x-0.5 dark:text-violet-400" />
                            </div>

                            <p class="mt-2 text-xs leading-6 text-neutral-500 dark:text-neutral-400">
                                {{ $category->children->isNotEmpty() ? $category->children->pluck('name')->join('، ') : 'فرص في هذا المجال' }}
                            </p>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="mx-auto w-full max-w-6xl px-4 pb-16 sm:px-6">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-bl from-violet-600 via-violet-700 to-indigo-950 px-6 py-12 text-white sm:px-12">
                    <div class="pointer-events-none absolute -top-20 -start-10 size-56 rounded-full bg-white/10 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-24 -end-10 size-64 rounded-full bg-violet-300/20 blur-3xl"></div>

                    <div class="relative flex flex-col items-start justify-between gap-6 md:flex-row md:items-center">
                        <div>
                            <h2 class="text-2xl font-bold sm:text-3xl">وظيفتك القادمة على بُعد خطوة</h2>
                            <p class="mt-2 text-sm leading-7 text-violet-50/90">أنشئ حسابك المجاني اليوم وابدأ بالتقديم خلال دقائق.</p>
                        </div>

                        <a
                            href="{{ $isAuthed ? route('jobs.index') : route('register') }}"
                            class="shrink-0 rounded-xl bg-white px-6 py-3 text-sm font-bold text-violet-700 shadow-lg shadow-indigo-950/30 transition hover:bg-violet-50"
                        >
                            {{ $isAuthed ? 'تصفح الوظائف' : 'إنشاء حساب مجاني' }}
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-neutral-200 bg-white dark:border-zinc-800 dark:bg-zinc-950">
            <div class="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-10 sm:px-6 md:flex-row md:items-center md:justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center overflow-hidden rounded-lg bg-white ring-1 ring-black/5 dark:ring-white/10">
                        <img src="{{ asset('images/jobnest-mark.png') }}" alt="" class="size-8 object-contain" />
                    </span>

                    <span>
                        <span class="block text-sm font-bold">JobNest</span>
                        <span class="block text-[11px] text-neutral-500 dark:text-neutral-400">وظائف عربية موثوقة</span>
                    </span>
                </a>

                <nav class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-neutral-500 dark:text-neutral-400">
                    <a href="#jobs" class="transition hover:text-violet-700 dark:hover:text-violet-300">الوظائف</a>
                    <a href="#how" class="transition hover:text-violet-700 dark:hover:text-violet-300">كيف يعمل</a>
                    <a href="{{ route('login') }}" class="transition hover:text-violet-700 dark:hover:text-violet-300">تسجيل الدخول</a>
                    <a href="{{ route('register') }}" class="transition hover:text-violet-700 dark:hover:text-violet-300">إنشاء حساب</a>
                </nav>

                <p class="text-xs text-neutral-500 dark:text-neutral-400">© {{ now()->year }} JobNest. جميع الحقوق محفوظة.</p>
            </div>
        </footer>
    </body>
</html>
