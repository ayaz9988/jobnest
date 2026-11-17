<?php

namespace App\Filament\Resources\Admins\Pages;

use App\Filament\Resources\Admins\AdminResource;
use App\Models\Admin;
use Filament\Auth\Notifications\VerifyEmail;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    protected function afterCreate(): void
    {
        $admin = $this->record;
        if (! $admin instanceof Admin || $admin->hasVerifiedEmail()) {
            return;
        }

        $notification = app(VerifyEmail::class);
        $notification->url = Filament::getVerifyEmailUrl($admin);
        $admin->notify($notification);
    }
}
