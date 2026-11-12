<?php

namespace App\Filament\Resources\Admins\Schemas;

use App\Models\Admin;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AdminForm
{
    private static function isProtectedAdmin(?Admin $record): bool
    {
        if (! $record) {
            return false;
        }

        if ($record->is(auth('admin')->user())) {
            return true;
        }

        return $record->isSuperAdmin()
            && $record->is_active
            && Admin::query()
                ->where('role', 'super_admin')
                ->where('is_active', true)
                ->count() <= 1;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                Select::make('role')
                    ->options(['admin' => 'Admin', 'super_admin' => 'Super admin'])
                    ->disabled(fn (?Admin $record): bool => self::isProtectedAdmin($record)),
                Toggle::make('is_active')
                    ->required()
                    ->disabled(fn (?Admin $record): bool => self::isProtectedAdmin($record)),
            ]);
    }
}
