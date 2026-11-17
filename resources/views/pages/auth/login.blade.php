<x-layouts::auth :title="'تسجيل الدخول'">
    <div class="flex flex-col gap-6">
        <x-auth-header title="تسجيل الدخول إلى حسابك" description="أدخل بريدك الإلكتروني وكلمة المرور لتسجيل الدخول" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-passkey-verify />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="'البريد الإلكتروني'"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="'كلمة المرور'"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="'كلمة المرور'"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ 'هل نسيت كلمة المرور؟' }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" label="تذكرني" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ 'تسجيل الدخول' }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
            <span>{{ 'ليس لديك حساب؟' }}</span>
            <flux:link :href="route('register')" wire:navigate>{{ 'إنشاء حساب' }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
