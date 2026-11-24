<?php

namespace App\Filament\Resources\Admins\Schemas;

use App\Models\Admin;
use App\Support\AdminTranslations;
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
                    ->label(AdminTranslations::field('name'))
                    ->required(),
                TextInput::make('email')
                    ->label(AdminTranslations::field('email'))
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('password')
                    ->label(AdminTranslations::field('password'))
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                Select::make('role')
                    ->label(AdminTranslations::field('role'))
                    ->options(AdminTranslations::options('admin_roles'))
                    ->default('admin')
                    ->required()
                    ->disabled(fn (?Admin $record): bool => self::isProtectedAdmin($record)),
                Toggle::make('is_active')
                    ->label(AdminTranslations::field('is_active'))
                    ->default(true)
                    ->disabled(fn (?Admin $record): bool => self::isProtectedAdmin($record)),
            ]);
    }
}
