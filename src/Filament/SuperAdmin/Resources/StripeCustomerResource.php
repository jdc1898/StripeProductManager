<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\StripeCustomerResource\Pages;
use App\Models\StripeCustomer;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;

class StripeCustomerResource extends Resource
{
    protected static ?string $model = StripeCustomer::class;

    protected static ?string $navigationGroup = 'Customers';

    protected static ?string $navigationLabel = 'Customers';

    protected static ?string $slug = 'stripe-customers';

    protected static ?int $navigationSort = 1;

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('stripe_id')->label('Stripe ID')->searchable(),
                TextColumn::make('name')->label('Name')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('phone')->label('Phone'),
                TextColumn::make('balance')->label('Balance')->money('USD'),
                TextColumn::make('currency')->label('Currency'),
                TextColumn::make('description')->label('Description')->limit(50),
                BadgeColumn::make('delinquent')->label('Delinquent')->colors(['danger' => true, 'success' => false]),
                TextColumn::make('created')->label('Created')->dateTime(),
            ])
            ->defaultSort('created', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStripeCustomers::route('/'),
            'view' => Pages\ViewStripeCustomer::route('/{record}'),
        ];
    }
}
