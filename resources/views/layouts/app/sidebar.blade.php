<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <flux:sidebar.header class="pb-2">
                <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3" wire:navigate>
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white shadow-sm shadow-emerald-900/20">
                        <x-app-logo-icon class="size-5" />
                    </span>

                    <span class="min-w-0">
                        <span class="block truncate text-sm font-bold tracking-tight text-neutral-900 dark:text-white">
                            {{ config('app.name', 'JobNest') }}
                        </span>
                        <span class="block truncate text-[11px] font-medium text-neutral-500 dark:text-neutral-400">
                            وظائف عربية موثوقة
                        </span>
                    </span>
                </a>

                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group heading="أساسي" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        لوحة التحكم
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="briefcase" :href="route('jobs.index')" :current="request()->routeIs('jobs.index', 'jobs.show')" wire:navigate>
                        الوظائف
                    </flux:sidebar.item>
                    @if (auth()->user()->isSeeker())
                        <flux:sidebar.item icon="bookmark" :href="route('jobs.saved')" :current="request()->routeIs('jobs.saved')" wire:navigate>
                            الوظائف المحفوظة
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="route('applications.index')" :current="request()->routeIs('applications.index', 'applications.show')" wire:navigate>
                            طلباتي
                        </flux:sidebar.item>
                    @endif
                    <flux:sidebar.item
                        icon="bell"
                        :href="route('notifications.index')"
                        :current="request()->routeIs('notifications.index')"
                        :badge="auth()->user()->unreadNotifications()->count() ?: null"
                        badge-color="red"
                        wire:navigate
                    >
                        الإشعارات
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group heading="حسابي" class="grid">
                    <flux:sidebar.item icon="user" :href="route('profile.show')" :current="request()->routeIs('profile.show')" wire:navigate>
                        ملفي الشخصي
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile Header -->
        <flux:header class="border-b border-zinc-200 bg-white/90 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/90 lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <a href="{{ route('dashboard') }}" class="flex items-center gap-2" wire:navigate>
                <span class="flex size-7 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-700 text-white">
                    <x-app-logo-icon class="size-4" />
                </span>
                <span class="text-sm font-bold text-neutral-900 dark:text-white">
                    {{ config('app.name', 'JobNest') }}
                </span>
            </a>

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            الإعدادات
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            تسجيل الخروج
                        </flux:menu.item>                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
