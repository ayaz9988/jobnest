<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Filament\Resources\Applications\Actions\ApplicationTransitions;
use App\Support\AdminTranslations;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(AdminTranslations::field('applicant'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jobListing.title')
                    ->label(AdminTranslations::field('job_listing'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('resume.file_name')
                    ->label(AdminTranslations::field('resume'))
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(AdminTranslations::field('status'))
                    ->formatStateUsing(fn (?string $state): string => AdminTranslations::option('application_statuses', $state))
                    ->badge(),
                TextColumn::make('applied_at')
                    ->label(AdminTranslations::field('applied_at'))
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
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(AdminTranslations::field('status'))
                    ->options(AdminTranslations::options('application_statuses')),
                SelectFilter::make('job_listing_id')
                    ->label(AdminTranslations::field('job_listing'))
                    ->relationship('jobListing', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                ...ApplicationTransitions::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords('delete'),
                ]),
            ]);
    }
}
