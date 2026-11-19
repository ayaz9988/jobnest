<?php

namespace App\Filament\Widgets;

use App\Models\JobListing;
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
            ->heading('Latest job listings')
            ->query(
                JobListing::query()
                    ->with(['postedBy', 'category'])
                    ->latest('created_at')
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Title'),

                TextColumn::make('postedBy.name')
                    ->label('Employer')
                    ->placeholder('—'),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'open' => 'success',
                        'closed' => 'warning',
                        'filled' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('published_at')
                    ->label('Published at')
                    ->dateTime()
                    ->placeholder('—'),
            ])
            ->paginated(false);
    }
}
