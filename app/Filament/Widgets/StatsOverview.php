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
                ->label(__('admin.widgets.stats.users.label'))
                ->description(__('admin.widgets.stats.users.description'))
                ->icon(Heroicon::OutlinedUsers)
                ->color('primary'),
            Stat::make('Seekers', User::query()->where('role', 'seeker')->count())
                ->label(__('admin.widgets.stats.seekers.label'))
                ->description(__('admin.widgets.stats.seekers.description'))
                ->icon(Heroicon::OutlinedUser)
                ->color('info'),
            Stat::make('Employers', User::query()->where('role', 'employer')->count())
                ->label(__('admin.widgets.stats.employers.label'))
                ->description(__('admin.widgets.stats.employers.description'))
                ->icon(Heroicon::OutlinedBuildingOffice)
                ->color('warning'),
            Stat::make('Open jobs', JobListing::query()->open()->count())
                ->label(__('admin.widgets.stats.open_jobs.label'))
                ->description(__('admin.widgets.stats.open_jobs.description'))
                ->icon(Heroicon::OutlinedBriefcase)
                ->color('success'),
            Stat::make('Applications', Application::query()->count())
                ->label(__('admin.widgets.stats.applications.label'))
                ->description(__('admin.widgets.stats.applications.description'))
                ->icon(Heroicon::OutlinedDocumentText)
                ->color('primary'),
        ];
    }
}
