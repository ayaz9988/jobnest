<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Support\AdminTranslations;
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
            ->heading(__('admin.widgets.latest_applications.heading'))
            ->query(fn (): Builder => Application::query()
                ->with(['user', 'jobListing'])
                ->latest('applied_at')
                ->limit(8)
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label(AdminTranslations::field('applicant'))
                    ->placeholder('-'),
                TextColumn::make('jobListing.title')
                    ->label(AdminTranslations::field('job_listing'))
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label(AdminTranslations::field('status'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => AdminTranslations::option('application_statuses', $state))
                    ->color(fn (string $state): string => match ($state) {
                        'applied' => 'info',
                        'reviewed' => 'warning',
                        'interview' => 'primary',
                        'hired' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('applied_at')
                    ->label(AdminTranslations::field('applied_at'))
                    ->dateTime(),
            ])
            ->paginated(false);
    }
}
