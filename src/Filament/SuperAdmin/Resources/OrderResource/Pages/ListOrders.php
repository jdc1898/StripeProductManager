<?php

namespace App\Filament\SuperAdmin\Resources\OrderResource\Pages;

use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\SuperAdmin\Resources\OrderResource;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),

            'paid' => Tab::make('Paid')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'paid');
                }),

            'open' => Tab::make('Open')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'open');
                }),

            'draft' => Tab::make('Draft')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'draft');
                }),

            'void' => Tab::make('Void')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'void');
                }),

            'uncollectible' => Tab::make('Uncollectible')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'uncollectible');
                }),

            'deleted' => Tab::make('Deleted')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'deleted');
                }),

            'subscription' => Tab::make('Subscription')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('billing_reason', 'subscription_cycle');
                }),

            'manual' => Tab::make('Manual')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('billing_reason', 'manual');
                }),
        ];
    }
}
