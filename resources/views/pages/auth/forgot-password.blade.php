<x-layouts::auth :title="'نسيت كلمة المرور'">
    <div class="flex flex-col gap-6">
        <x-auth-header title="نسيت كلمة المرور" description="أدخل بريدك الإلكتروني لاستلام رابط إعادة تعيين كلمة المرور" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="'البريد الإلكتروني'"
                type="email"
                required
                autofocus
                placeholder="email@example.com"
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-link-button">
                {{ 'إرسال رابط إعادة التعيين' }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
            <span>{{ 'أو عد إلى' }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ 'تسجيل الدخول' }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
