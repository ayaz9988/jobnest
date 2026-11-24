<?php

namespace App\Filament\Resources\Resumes\Tables;

use App\Models\Resume;
use App\Support\AdminTranslations;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class ResumesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(AdminTranslations::field('user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('file_name')
                    ->label(AdminTranslations::field('file_name'))
                    ->searchable(),
                TextColumn::make('mime_type')
                    ->label(AdminTranslations::field('mime_type'))
                    ->searchable(),
                TextColumn::make('file_size')
                    ->label(AdminTranslations::field('file_size'))
                    ->formatStateUsing(fn (?int $state): string => $state ? Number::fileSize($state, precision: 1) : '-')
                    ->sortable(),
                IconColumn::make('is_primary')
                    ->label(AdminTranslations::field('is_primary'))
                    ->boolean(),
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
                Action::make('download')
                    ->label(__('admin.actions.download'))
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->url(fn (Resume $record): string => route('filament.admin.resumes.download', ['resume' => $record]))
                    ->openUrlInNewTab()
                    ->authorize('download'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords('delete'),
                ]),
            ]);
    }
}
