<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\StripePromotionCodeResource\Pages;
use App\Models\StripePromotionCode;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;

class StripePromotionCodeResource extends Resource
{
    protected static ?string $model = StripePromotionCode::class;

    protected static ?string $navigationGroup = 'Product Management';

    protected static ?string $navigationLabel = 'Promotion Codes';

    protected static ?string $slug = 'stripe-promotion-codes';

    protected static ?int $navigationSort = 5;

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('stripe_id')->label('ID')->searchable(),
                TextColumn::make('code')->label('Code')->searchable(),
                TextColumn::make('coupon')->label('Coupon'),
                TextColumn::make('times_redeemed')->label('Redeemed'),
                TextColumn::make('max_redemptions')->label('Max Redemptions'),
                TextColumn::make('expires_at')->label('Expires')->dateTime(),
                BadgeColumn::make('active')->label('Active')->colors(['success' => true, 'danger' => false]),
            ])

            ->defaultSort('created', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStripePromotionCodes::route('/'),
            'view' => Pages\ViewStripePromotionCode::route('/{record}'),
        ];
    }
}
