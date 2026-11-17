<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Filament\Resources\Applications\Actions\ApplicationTransitions;
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
                    ->label('Applicant')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jobListing.title')
                    ->label('Job listing')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('resume.file_name')
                    ->label('Resume')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('applied_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'applied' => 'Applied',
                        'reviewed' => 'Reviewed',
                        'interview' => 'Interview',
                        'withdraw' => 'Withdraw',
                        'hired' => 'Hired',
                        'rejected' => 'Rejected',
                    ]),
                SelectFilter::make('job_listing_id')
                    ->label('Job listing')
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
