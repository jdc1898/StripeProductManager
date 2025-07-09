<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Models\StripePrice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PriceStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $prices = StripePrice::query();

        $activePrices = StripePrice::where(function ($query) {
            $query->whereHas('stripeProduct', function ($productQuery) {
                $productQuery->where('active', true);
            })->orWhereHas('activeSubscriptions');
        })->count();

        return [
            Stat::make('Total Prices', $prices->count())
                ->icon('heroicon-m-squares-2x2')
                ->color('gray'),

            Stat::make('Active Prices', $activePrices)
                ->icon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Inactive Prices', $prices->count() - $activePrices)
                ->icon('heroicon-o-eye-slash')
                ->color('danger'),
        ];
    }
}
