<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\StripeTaxCodeResource\Pages;
use App\Models\StripeTaxCode;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;

class StripeTaxCodeResource extends Resource
{
    protected static ?string $model = StripeTaxCode::class;

    protected static ?string $navigationGroup = 'Product Management';

    protected static ?string $navigationLabel = 'Tax Codes';

    protected static ?string $slug = 'stripe-tax-codes';

    protected static ?int $navigationSort = 7;

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('stripe_id')->label('ID')->searchable(),
                TextColumn::make('name')->label('Name')->searchable(),
                TextColumn::make('description')->label('Description')->limit(50),
                TextColumn::make('category')->label('Category'),
                BadgeColumn::make('isCommonTaxCode')->label('Common')->colors(['success' => true, 'secondary' => false])->getStateUsing(fn ($record) => $record->isCommonTaxCode()),
            ])

            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStripeTaxCodes::route('/'),
            'view' => Pages\ViewStripeTaxCode::route('/{record}'),
        ];
    }
}
