<?php

namespace App\Filament\Widgets;

use App\Models\JobListing;
use App\Support\AdminTranslations;
use Filament\Widgets\ChartWidget;

class JobsByStatus extends ChartWidget
{
    protected static ?int $sort = 30;

    protected int|string|array $columnSpan = 1;

    public function getHeading(): ?string
    {
        return __('admin.widgets.jobs_by_status.heading');
    }

    public function getDescription(): ?string
    {
        return __('admin.widgets.jobs_by_status.description');
    }

    protected function getData(): array
    {
        $statuses = AdminTranslations::options('job_statuses');

        $counts = JobListing::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'datasets' => [
                [
                    'label' => __('admin.widgets.jobs_by_status.dataset'),
                    'data' => array_map(
                        fn (string $status): int => (int) ($counts[$status] ?? 0),
                        array_keys($statuses),
                    ),
                    'backgroundColor' => ['#9ca3af', '#22c55e', '#f59e0b', '#3b82f6'],
                ],
            ],
            'labels' => array_values($statuses),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
        ];
    }
}
