<?php

namespace App\Filament\Widgets;

use App\Models\JobListing;
use App\Support\AdminTranslations;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestJobListings extends TableWidget
{
    protected static ?int $sort = 50;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('admin.widgets.latest_job_listings.heading'))
            ->query(
                JobListing::query()
                    ->with(['postedBy', 'category'])
                    ->latest('created_at')
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('title')
                    ->label(AdminTranslations::field('title')),
                TextColumn::make('postedBy.name')
                    ->label(AdminTranslations::field('employer'))
                    ->placeholder('—'),
                TextColumn::make('category.name')
                    ->label(AdminTranslations::field('category'))
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label(AdminTranslations::field('status'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => AdminTranslations::option('job_statuses', $state))
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'open' => 'success',
                        'closed' => 'warning',
                        'filled' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('published_at')
                    ->label(AdminTranslations::field('published_at'))
                    ->dateTime()
                    ->placeholder('—'),
            ])
            ->paginated(false);
    }
}
