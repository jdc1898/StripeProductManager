<?php

namespace App\Filament\SuperAdmin\Resources\StripePromotionCodeResource\Pages;

use App\Filament\SuperAdmin\Resources\StripePromotionCodeResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListStripePromotionCodes extends ListRecords
{
    protected static string $resource = StripePromotionCodeResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'active' => Tab::make('Active')->modifyQueryUsing(fn ($query) => $query->where('active', true)),
            'inactive' => Tab::make('Inactive')->modifyQueryUsing(fn ($query) => $query->where('active', false)),
            'expired' => Tab::make('Expired')->modifyQueryUsing(fn ($query) => $query->whereNotNull('expires_at')->where('expires_at', '<', now())),
        ];
    }
}
