<?php

namespace App\Filament\SuperAdmin\Resources\StripeProductResource\Pages;

use App\Filament\Forms\Builders\ProductFormBuilder;
use App\Filament\SuperAdmin\Resources\StripeProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct extends ViewRecord
{
    protected static string $resource = StripeProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->form(ProductFormBuilder::getAdvancedPricingForm())->slideOver(),
        ];
    }
}
