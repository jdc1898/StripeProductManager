<?php

namespace App\Filament\SuperAdmin\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use App\Filament\SuperAdmin\Resources\StripeDiscountResource\Pages;
use App\Models\StripeDiscount;

class StripeDiscountResource extends Resource
{
    protected static ?string $model = StripeDiscount::class;

    protected static ?string $navigationGroup = 'Product Management';

    protected static ?string $navigationLabel = 'Discounts';

    protected static ?string $slug = 'stripe-discounts';

    protected static ?int $navigationSort = 3;

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('stripe_id')->label('ID')->searchable(),
                TextColumn::make('customer')->label('Customer'),
                TextColumn::make('subscription')->label('Subscription'),
                TextColumn::make('coupon')->label('Coupon'),
                TextColumn::make('start')->label('Start')->dateTime(),
                TextColumn::make('end')->label('End')->dateTime(),
                BadgeColumn::make('isActive')
                    ->label('Active')
                    ->colors(['success' => true, 'danger' => false])
                    ->getStateUsing(fn ($record) => $record->isActive()),
            ])
            ->defaultSort('start', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStripeDiscounts::route('/'),
            'view' => Pages\ViewStripeDiscount::route('/{record}'),
        ];
    }
}
