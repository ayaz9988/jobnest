<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;

class ApplicationsChart extends ChartWidget
{
    protected static ?int $sort = 20;

    protected int|string|array $columnSpan = 1;

    protected string $color = 'warning';

    public function getHeading(): ?string
    {
        return __('admin.widgets.applications_chart.heading');
    }

    public function getDescription(): ?string
    {
        return __('admin.widgets.applications_chart.description');
    }

    protected function getData(): array
    {
        $end = now();
        $start = $end->copy()->subDays(29)->startOfDay();
        $end = $end->copy()->endOfDay();

        $counts = Application::query()
            ->selectRaw('DATE(applied_at) as day, COUNT(*) as total')
            ->whereBetween('applied_at', [$start, $end])
            ->groupByRaw('DATE(applied_at)')
            ->pluck('total', 'day');
        $labels = [];
        $data = [];

        foreach (CarbonPeriod::create($start, '1 day', $end) as $date) {
            $labels[] = $date->translatedFormat('M j');
            $data[] = (int) ($counts[$date->toDateString()] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => __('admin.widgets.applications_chart.dataset'),
                    'data' => $data,
                    'tension' => 0.3,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                ],
                'x' => [
                    'ticks' => ['maxTicksLimit' => 10],
                ],
            ],
        ];
    }
}
