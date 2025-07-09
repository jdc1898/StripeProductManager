<?php

namespace App\Filament\SuperAdmin\Resources\SubscriptionResource\Pages;

use App\Filament\SuperAdmin\Resources\SubscriptionResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

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

            'active' => Tab::make('Active')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('stripe_status', 'active');
                }),

            'inactive' => Tab::make('Inactive')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('stripe_status', 'paused');
                }),

            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(function ($query) {
                    return $query->whereIn('stripe_status', ['incomplete', 'incomplete_expired', 'trialing']);
                }),

            'canceled' => Tab::make('Canceled')
                ->modifyQueryUsing(function ($query) {
                    return $query->whereIn('stripe_status', ['canceled', 'unpaid']);
                }),

            'past_due' => Tab::make('Past Due')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('stripe_status', 'past_due');
                }),
        ];
    }
}
