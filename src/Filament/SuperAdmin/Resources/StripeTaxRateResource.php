<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\StripeTaxRateResource\Pages;
use App\Models\StripeTaxRate;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;

class StripeTaxRateResource extends Resource
{
    protected static ?string $model = StripeTaxRate::class;

    protected static ?string $navigationGroup = 'Product Management';

    protected static ?string $navigationLabel = 'Tax Rates';

    protected static ?string $slug = 'stripe-tax-rates';

    protected static ?int $navigationSort = 6;

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('stripe_id')->label('ID')->searchable(),
                TextColumn::make('display_name')->label('Name')->searchable(),
                TextColumn::make('formatted_percentage')->label('Rate'),
                TextColumn::make('country')->label('Country'),
                TextColumn::make('state')->label('State'),
                TextColumn::make('jurisdiction')->label('Jurisdiction'),
                BadgeColumn::make('inclusive')->label('Inclusive')->colors(['success' => true, 'secondary' => false]),
                BadgeColumn::make('active')->label('Active')->colors(['success' => true, 'danger' => false]),
            ])

            ->defaultSort('created', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStripeTaxRates::route('/'),
            'view' => Pages\ViewStripeTaxRate::route('/{record}'),
        ];
    }
}
