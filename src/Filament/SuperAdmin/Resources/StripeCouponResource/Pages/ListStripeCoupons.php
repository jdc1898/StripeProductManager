<?php

namespace App\Filament\SuperAdmin\Resources\StripeCouponResource\Pages;

use App\Filament\SuperAdmin\Resources\StripeCouponResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListStripeCoupons extends ListRecords
{
    protected static string $resource = StripeCouponResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'valid' => Tab::make('Valid')->modifyQueryUsing(fn ($query) => $query->where('valid', true)),
            'invalid' => Tab::make('Invalid')->modifyQueryUsing(fn ($query) => $query->where('valid', false)),
        ];
    }
}
