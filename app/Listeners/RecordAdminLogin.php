<?php

namespace App\Listeners;

use App\Models\Admin;
use Illuminate\Auth\Events\Login;

class RecordAdminLogin
{
    public function handle(Login $event): void
    {
        if ($event->guard !== 'admin' || ! $event->user instanceof Admin) {
            return;
        }

        $event->user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ])->saveQuietly();
    }
}
