<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Models\StripeTransaction;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class MrrChartStatsWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Monthly recurring revenue (MRR) overview';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = [
        'default' => 1,
        'sm' => 2,
        'xl' => 2,
    ];

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $period = $this->filters['period'] ?? 'month';

        $data = Trend::model(StripeTransaction::class)
            ->between(
                start: $startDate ? now()->parse($startDate) : now()->startOfMonth(),
                end: $endDate ? now()->parse($endDate) : now()->endOfMonth(),
            );

        if ($period === 'day') {
            $data = $data->perDay()->sum('amount');
        } elseif ($period === 'week') {
            $data = $data->perWeek()->sum('amount');
        } elseif ($period === 'year') {
            $data = $data->perYear()->sum('amount');
        } else {
            $data = $data->perMonth()->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Monthly Recurring Revenue (MRR)',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    public function getDescription(): ?string
    {
        return 'MRR takes into acsum only active subscriptions (no trials).';
    }

    protected function getType(): string
    {
        return 'line';
    }
}
