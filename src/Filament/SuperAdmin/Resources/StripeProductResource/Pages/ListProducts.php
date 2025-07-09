<?php

namespace App\Filament\SuperAdmin\Resources\StripeProductResource\Pages;

use App\Filament\SuperAdmin\Resources\StripeProductResource;
use App\Filament\SuperAdmin\Widgets\ProductStatsWidget;
use App\Models\StripeProduct;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = StripeProductResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            ProductStatsWidget::class,
        ];
    }


    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Products')
                ->icon('heroicon-m-squares-2x2')
                ->badgeColor('gray')
                ->badge(StripeProduct::count()),
            'active' => Tab::make('Active')
                ->icon('heroicon-o-eye')
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', true))
                ->badge(StripeProduct::where('active', true)->count()),
            'inactive' => Tab::make('Inactive')
                ->icon('heroicon-o-eye-slash')
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', false))
                ->badge(StripeProduct::where('active', false)->count()),
        ];
    }

    public function getDefaultActiveTab(): string
    {
        return 'active';
    }


}
