<?php

namespace App\Filament\SuperAdmin\Resources\StripeTaxCodeResource\Pages;

use App\Filament\SuperAdmin\Resources\StripeTaxCodeResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListStripeTaxCodes extends ListRecords
{
    protected static string $resource = StripeTaxCodeResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'common' => Tab::make('Common')->modifyQueryUsing(fn ($query) => $query->whereIn('stripe_id', [
                'txcd_99999999', 'txcd_99999998', 'txcd_99999997', 'txcd_99999996', 'txcd_99999995',
            ])),
        ];
    }
}
