<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestApplications extends TableWidget
{
    protected static ?int $sort = 40;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Latest applications')
            ->query(fn (): Builder => Application::query()
                ->with(['user', 'jobListing'])
                ->latest('applied_at')
                ->limit(8)
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Applicant')
                    ->placeholder('-'),
                TextColumn::make('jobListing.title')
                    ->label('Job listing')
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'applied' => 'info',
                        'reviewed' => 'warning',
                        'interview' => 'primary',
                        'hired' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('applied_at')
                    ->label('Applied at')
                    ->dateTime(),
            ])
            ->paginated(false);
    }
}
