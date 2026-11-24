<?php

namespace App\Filament\Resources\JobListings\Tables;

use App\Support\AdminTranslations;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JobListingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('postedBy.name')
                    ->label(AdminTranslations::field('posted_by'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label(AdminTranslations::field('category'))
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label(AdminTranslations::field('title'))
                    ->searchable(),
                TextColumn::make('employment_type')
                    ->label(AdminTranslations::field('employment_type'))
                    ->formatStateUsing(fn (?string $state): string => AdminTranslations::option('employment_types', $state)
                    )
                    ->badge(),
                TextColumn::make('workplace_type')
                    ->label(AdminTranslations::field('workplace_type'))
                    ->formatStateUsing(fn (?string $state): string => AdminTranslations::option('workplace_types', $state)
                    )
                    ->badge(),
                TextColumn::make('location')
                    ->label(AdminTranslations::field('location'))
                    ->searchable(),
                TextColumn::make('salary_min')
                    ->label(AdminTranslations::field('salary_min'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('salary_max')
                    ->label(AdminTranslations::field('salary_max'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('salary_currency')
                    ->label(AdminTranslations::field('salary_currency'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(AdminTranslations::field('status'))
                    ->formatStateUsing(fn (?string $state): string => AdminTranslations::option('job_statuses', $state)
                    )
                    ->badge(),
                TextColumn::make('expires_at')
                    ->label(AdminTranslations::field('expires_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label(AdminTranslations::field('published_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->label(AdminTranslations::field('deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
