<?php

namespace App\Filament\SuperAdmin\Resources\StripeTaxRateResource\Pages;

use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\SuperAdmin\Resources\StripeTaxRateResource;

class ListStripeTaxRates extends ListRecords
{
    protected static string $resource = StripeTaxRateResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'active' => Tab::make('Active')->modifyQueryUsing(fn ($query) => $query->where('active', true)),
            'inactive' => Tab::make('Inactive')->modifyQueryUsing(fn ($query) => $query->where('active', false)),
            'inclusive' => Tab::make('Inclusive')->modifyQueryUsing(fn ($query) => $query->where('inclusive', true)),
            'exclusive' => Tab::make('Exclusive')->modifyQueryUsing(fn ($query) => $query->where('inclusive', false)),
        ];
    }
}
