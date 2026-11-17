<x-layouts::auth :title="'تأكيد البريد الإلكتروني'">
    <div class="mt-4 flex flex-col gap-6">
        <flux:text class="text-center">
            {{ 'يرجى تأكيد بريدك الإلكتروني بالنقر على الرابط الذي أرسلناه إليك.' }}
        </flux:text>

        @if (session('status') == 'verification-link-sent')
            <flux:text class="text-center font-medium !dark:text-green-400 !text-green-600">
                {{ 'تم إرسال رابط تحقق جديد إلى البريد الإلكتروني الذي استخدمته عند التسجيل.' }}
            </flux:text>
        @endif

        <div class="flex flex-col items-center justify-between space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <flux:button type="submit" variant="primary" class="w-full">
                    {{ 'إعادة إرسال رسالة التحقق' }}
                </flux:button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:button variant="ghost" type="submit" class="text-sm cursor-pointer" data-test="logout-button">
                    {{ 'تسجيل الخروج' }}
                </flux:button>
            </form>
        </div>
    </div>
</x-layouts::auth>
