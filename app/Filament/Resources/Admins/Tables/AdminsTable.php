<?php

namespace App\Filament\Resources\Admins\Tables;

use App\Support\AdminTranslations;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdminsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(AdminTranslations::field('name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(AdminTranslations::field('email'))
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->label(AdminTranslations::field('email_verified_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('role')
                    ->label(AdminTranslations::field('role'))
                    ->formatStateUsing(fn (?string $state): string => AdminTranslations::option('admin_roles', $state))
                    ->badge(),
                IconColumn::make('is_active')
                    ->label(AdminTranslations::field('is_active'))
                    ->boolean(),
                TextColumn::make('last_login_at')
                    ->label(AdminTranslations::field('last_login_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_login_ip')
                    ->label(AdminTranslations::field('last_login_ip'))
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(AdminTranslations::field('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(AdminTranslations::field('updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
                Filter::make('is_active')
                    ->label(__('admin.filters.active_admins'))
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
