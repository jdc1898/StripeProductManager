<?php

namespace App\Filament\SuperAdmin\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\SuperAdmin\Resources\StripePriceResource\Pages;
use App\Filament\SuperAdmin\Widgets\PriceStatsWidget;
use App\Models\StripePrice;
use Illuminate\Database\Eloquent\Builder;

class StripePriceResource extends Resource
{
    protected static ?string $model = StripePrice::class;

    protected static ?string $navigationGroup = 'Product Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Prices';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where(function (Builder $query) {
            $query->whereHas('stripeProduct', function (Builder $productQuery) {
                $productQuery->where('active', true);
            })->orWhereHas('activeSubscriptions');
        })->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getHeaderWidgets(): array
    {
        return [
            PriceStatsWidget::class,
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('stripeProduct'))
            ->columns([
                Tables\Columns\TextColumn::make('stripe_id')
                    ->label('Stripe ID')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('stripeProduct.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit_amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state) => '$'.number_format($state / 100, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('currency')
                    ->label('Currency')
                    ->formatStateUsing(fn ($state) => strtoupper($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn ($state): string => $state ? 'success' : 'danger')
                    ->sortable(),

                Tables\Columns\IconColumn::make('default')
                    ->label('Default')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->state(fn ($record): bool => $record && $record->stripeProduct?->default_price === $record->stripe_id)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stripeProduct')
                    ->relationship('stripeProduct', 'name')
                    ->label('Product')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-m-eye'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // No bulk actions for Stripe prices
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStripePrices::route('/'),
            'view' => Pages\ViewStripePrices::route('/{record}'),
        ];
    }
}
