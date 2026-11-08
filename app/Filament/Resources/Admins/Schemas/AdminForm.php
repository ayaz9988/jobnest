<?php

namespace App\Filament\Resources\Admins\Schemas;

use App\Models\Admin;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AdminForm
{
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
                    ->required(),
                Select::make('role')
                    ->options(['admin' => 'Admin', 'super_admin' => 'Super admin'])
                    ->disabled(function (?Admin $record) {
                        if (! $record) {
                            return false;
                        }

                        if ($record->is(auth('admin')->user())) {
                            return true;
                        }

                        return $record->isSuperAdmin()
                        && Admin::where('is_active', true)
                            ->where('role', 'super_admin')
                            ->count() <= 1;
                    }),
                Toggle::make('is_active')
                    ->required()
                    ->disabled(function (?Admin $record) {
                        if (! $record) {
                            return false;
                        }

                        if ($record->is(auth('admin')->user())) {
                            return true;
                        }

                        return $record->isSuperAdmin()
                        && Admin::where('is_active', true)
                            ->where('role', 'super_admin')
                            ->count() <= 1;
                    }),
            ]);
    }
}
