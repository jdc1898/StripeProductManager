<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Models\StripeProduct;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected static bool $isCollapsible = false;

    protected function getStats(): array
    {
        $products = StripeProduct::query();

        return [
            Stat::make('Total Products', $products->count())
                ->icon('heroicon-m-squares-2x2')
                ->color('gray'),

            Stat::make('Active Products', $products->where('active', true)->count())
                ->icon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Inactive Products', $products->where('active', false)->count())
                ->icon('heroicon-o-archive-box')
                ->color('danger'),
        ];
    }
}
