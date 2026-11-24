<?php

namespace App\Filament\Resources\Users\Tables;

use App\Support\AdminTranslations;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
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
                TextColumn::make('two_factor_confirmed_at')
                    ->label(AdminTranslations::field('two_factor_confirmed_at'))
                    ->dateTime()
                    ->sortable(),
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
                TextColumn::make('role')
                    ->label(AdminTranslations::field('role'))
                    ->formatStateUsing(fn (?string $state): string => AdminTranslations::option('user_roles', $state)
                    )
                    ->badge(),
                TextColumn::make('location')
                    ->label(AdminTranslations::field('location'))
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords('delete'),
                ]),
            ]);
    }
}
