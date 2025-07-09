<?php

namespace App\Filament\SuperAdmin\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use App\Filament\SuperAdmin\Resources\StripeCouponResource\Pages;
use App\Models\StripeCoupon;

class StripeCouponResource extends Resource
{
    protected static ?string $model = StripeCoupon::class;

    protected static ?string $navigationGroup = 'Product Management';

    protected static ?string $navigationLabel = 'Coupons';

    protected static ?string $slug = 'stripe-coupons';

    protected static ?int $navigationSort = 4;

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('stripe_id')->label('ID')->searchable(),
                TextColumn::make('name')->label('Name')->searchable(),
                TextColumn::make('discount_amount')->label('Discount'),
                TextColumn::make('duration')->label('Duration'),
                TextColumn::make('duration_in_months')->label('Months'),
                TextColumn::make('times_redeemed')->label('Redeemed'),
                TextColumn::make('max_redemptions')->label('Max Redemptions'),
                BadgeColumn::make('valid')->label('Valid')->colors(['success' => true, 'danger' => false]),
            ])

            ->defaultSort('created', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStripeCoupons::route('/'),
            'view' => Pages\ViewStripeCoupon::route('/{record}'),
        ];
    }
}
