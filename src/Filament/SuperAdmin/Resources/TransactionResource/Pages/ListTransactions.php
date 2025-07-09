<?php

namespace App\Filament\SuperAdmin\Resources\TransactionResource\Pages;

use App\Filament\SuperAdmin\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),

            'success' => Tab::make('Success')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'succeeded');
                }),

            'refunded' => Tab::make('Pending')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'refunded');
                }),

            'failed' => Tab::make('Failed')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'failed');
                }),

            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'pending');
                }),

            'disputed' => Tab::make('Disputed')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'disputed');
                }),
        ];
    }
}
