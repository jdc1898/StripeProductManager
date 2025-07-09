<?php

namespace App\Filament\SuperAdmin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MrrStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = [
        'default' => 8,
        'sm' => 8,
        'xl' => 8,
    ];

    protected function getStats(): array
    {
        $data = [
            'mrr' => 620.83,
            'decrease' => 192.50,
        ];

        $chartData = [
            7, 2, 10, 3, 15, 40, 17,
        ];

        $formattedMrr = '$'.number_format($data['mrr'], 2);
        $formattedDecrease = '$'.number_format($data['decrease'], 2);

        $descriptionIcon = 'heroicon-m-ellipsis-horizontal';

        if (count($chartData) >= 2) {
            $lastValue = $chartData[count($chartData) - 1];
            $secondLastValue = $chartData[count($chartData) - 2];

            if ($lastValue > $secondLastValue) {
                $description = 'Increased by '.$formattedDecrease;
                $descriptionIcon = 'heroicon-m-arrow-trending-up';
                $color = 'success';
            } elseif ($lastValue < $secondLastValue) {
                $description = 'Decreased by '.$formattedDecrease;
                $descriptionIcon = 'heroicon-m-arrow-trending-down';
                $color = 'danger';
            } else {
                $description = 'No change';
                $descriptionIcon = 'heroicon-m-minus';
                $color = 'gray';
            }
        }

        return [
            Stat::make('MRR', $formattedMrr)
                ->description($description)
                ->descriptionIcon($descriptionIcon)
                ->chart($chartData)
                ->color($color),
        ];
    }
}
