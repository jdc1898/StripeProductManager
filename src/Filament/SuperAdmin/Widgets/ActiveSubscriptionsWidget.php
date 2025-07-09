<?php

namespace App\Filament\SuperAdmin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActiveSubscriptionsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'default' => 1,
        'sm' => 1,
        'xl' => 1,
    ];

    protected function getStats(): array
    {
        $label = 'Active Subscriptions';

        $data = [
            'value' => 34,
            'change' => 2,
        ];

        $chartData = [
            7, 2, 10, 3, 15, 40, 17,
        ];

        $formattedData = $data['value'];
        $formattedChange = $data['change'];

        $descriptionIcon = 'heroicon-m-ellipsis-horizontal';

        if (count($chartData) >= 2) {
            $lastValue = $chartData[count($chartData) - 1];
            $secondLastValue = $chartData[count($chartData) - 2];

            if ($lastValue > $secondLastValue) {
                $description = 'Increased by '.$formattedChange;
                $descriptionIcon = 'heroicon-m-arrow-trending-up';
                $color = 'success';
            } elseif ($lastValue < $secondLastValue) {
                $description = 'Decreased by '.$formattedChange;
                $descriptionIcon = 'heroicon-m-arrow-trending-down';
                $color = 'danger';
            } else {
                $description = 'No change';
                $descriptionIcon = 'heroicon-m-minus';
                $color = 'gray';
            }
        }

        return [
            Stat::make($label, $formattedData)
                ->description($description)
                ->descriptionIcon($descriptionIcon)
                ->chart($chartData)
                ->color($color),
        ];
    }
}
