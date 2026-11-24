<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Support\AdminTranslations;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
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
                    ->required(),
                DateTimePicker::make('email_verified_at')
                    ->label(AdminTranslations::field('email_verified_at')),
                TextInput::make('password')
                    ->label(AdminTranslations::field('password'))
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                Select::make('role')
                    ->label(AdminTranslations::field('role'))
                    ->options(AdminTranslations::options('user_roles'))
                    ->default('seeker')
                    ->required(),
                Textarea::make('profile')
                    ->label(AdminTranslations::field('profile'))
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('location')
                    ->label(AdminTranslations::field('location'))
                    ->default(null),
            ]);
    }
}
