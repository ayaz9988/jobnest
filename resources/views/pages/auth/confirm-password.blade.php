<x-layouts::auth :title="'تأكيد كلمة المرور'">
    <div class="flex flex-col gap-6">
        <x-auth-header
            title="تأكيد كلمة المرور"
            description="هذه منطقة آمنة في التطبيق. يرجى تأكيد كلمة المرور للمتابعة."
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-passkey-verify
            options-route="passkey.confirm-options"
            submit-route="passkey.confirm"
            label="التأكيد بمفتاح المرور"
            loading-label="جارٍ التأكيد..."
            separator="أو التأكيد بكلمة المرور"
        />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="password"
                :label="'كلمة المرور'"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="'كلمة المرور'"
                viewable
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="confirm-password-button">
                {{ 'تأكيد' }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
