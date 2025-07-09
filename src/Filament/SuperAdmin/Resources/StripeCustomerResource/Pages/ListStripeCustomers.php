<?php

namespace App\Filament\SuperAdmin\Resources\StripeCustomerResource\Pages;

use App\Filament\SuperAdmin\Resources\StripeCustomerResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListStripeCustomers extends ListRecords
{
    protected static string $resource = StripeCustomerResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'delinquent' => Tab::make('Delinquent')->modifyQueryUsing(fn ($query) => $query->where('delinquent', true)),
            'current' => Tab::make('Current')->modifyQueryUsing(fn ($query) => $query->where('delinquent', false)),
        ];
    }
}
