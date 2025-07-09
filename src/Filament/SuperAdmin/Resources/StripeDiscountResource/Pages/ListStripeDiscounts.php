<?php

namespace App\Filament\SuperAdmin\Resources\StripeDiscountResource\Pages;

use App\Filament\SuperAdmin\Resources\StripeDiscountResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListStripeDiscounts extends ListRecords
{
    protected static string $resource = StripeDiscountResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'active' => Tab::make('Active')->modifyQueryUsing(fn ($query) => $query->whereRaw('1=1')), // Placeholder for now
            'expired' => Tab::make('Expired')->modifyQueryUsing(fn ($query) => $query->whereNotNull('end')->where('end', '<', now())),
        ];
    }
}
