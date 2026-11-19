<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Users', User::query()->count())
                ->description('All registered accounts')
                ->icon(Heroicon::OutlinedUsers)
                ->color('primary'),
            Stat::make('Seekers', User::query()->where('role', 'seeker')->count())
                ->description('Job Seekers')
                ->icon(Heroicon::OutlinedUser)
                ->color('info'),
            Stat::make('Employers', User::query()->where('role', 'employer')->count())
                ->description('Companies posting jobs')
                ->icon(Heroicon::OutlinedBuildingOffice)
                ->color('warning'),
            Stat::make('Open jobs', JobListing::query()->open()->count())
                ->description('Visible to seekers')
                ->icon(Heroicon::OutlinedBriefcase)
                ->color('success'),
            Stat::make('Applications', Application::query()->count())
                ->description('All statuses')
                ->icon(Heroicon::OutlinedDocumentText)
                ->color('primary'),
        ];
    }
}
