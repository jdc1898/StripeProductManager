<?php

namespace App\Filament\SuperAdmin\Resources\StripePriceResource\Pages;

use App\Filament\SuperAdmin\Resources\StripePriceResource;
use App\Filament\SuperAdmin\Widgets\PriceStatsWidget;
use App\Models\StripePrice;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListStripePrices extends ListRecords
{
    protected static string $resource = StripePriceResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Prices')
                ->icon('heroicon-m-squares-2x2')
                ->badgeColor('gray')
                ->badge(StripePrice::count()),
            'active' => Tab::make('Active')
                ->icon('heroicon-o-eye')
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->where(function (Builder $query) {
                    $query->whereHas('stripeProduct', function (Builder $productQuery) {
                        $productQuery->where('active', true);
                    })->orWhereHas('activeSubscriptions');
                }))
                ->badge(StripePrice::where(function (Builder $query) {
                    $query->whereHas('stripeProduct', function (Builder $productQuery) {
                        $productQuery->where('active', true);
                    })->orWhereHas('activeSubscriptions');
                })->count()),
            'inactive' => Tab::make('Inactive')
                ->icon('heroicon-o-eye-slash')
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where(function (Builder $query) {
                    $query->whereDoesntHave('stripeProduct', function (Builder $productQuery) {
                        $productQuery->where('active', true);
                    })->whereDoesntHave('activeSubscriptions');
                }))
                ->badge(StripePrice::where(function (Builder $query) {
                    $query->whereDoesntHave('stripeProduct', function (Builder $productQuery) {
                        $productQuery->where('active', true);
                    })->whereDoesntHave('activeSubscriptions');
                })->count()),
            'default' => Tab::make('Default')
                ->icon('heroicon-o-shield-check')
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->whereHas('stripeProduct', function (Builder $query) {
                        $query->whereColumn('default_price', 'stripe_prices.stripe_id');
                    })
                )
                ->badgeColor('gray')
                ->badge(StripePrice::whereHas('stripeProduct', function (Builder $query) {
                    $query->whereColumn('default_price', 'stripe_prices.stripe_id');
                })->count()),
        ];
    }

    public function getDefaultActiveTab(): string
    {
        return 'active';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // PriceStatsWidget::class,
        ];
    }
}
