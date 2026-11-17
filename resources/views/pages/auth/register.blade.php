<x-layouts::auth :title="'إنشاء حساب'">
    <div class="flex flex-col gap-6">
        <x-auth-header title="أنشئ حسابك" description="أدخل بياناتك أدناه لإنشاء حساب جديد" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="'الاسم'"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="'الاسم الكامل'"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="'البريد الإلكتروني'"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="'كلمة المرور'"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="'كلمة المرور'"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="'تأكيد كلمة المرور'"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="'تأكيد كلمة المرور'"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ 'إنشاء الحساب' }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ 'لديك حساب بالفعل؟' }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ 'تسجيل الدخول' }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
