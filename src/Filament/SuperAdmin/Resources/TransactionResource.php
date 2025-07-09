<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\TransactionResource\Pages;
use App\Models\StripeTransaction;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class TransactionResource extends Resource
{
    protected static ?string $model = StripeTransaction::class;

    protected static ?string $modelLabel = 'Transaction';

    protected static ?string $navigationGroup = 'Revenue';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Section::make('Transaction Details')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('charge_id')
                            ->label('Charge ID')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('customer_id')
                            ->label('Customer ID')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('tenant_name')
                            ->label('Tenant')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('amount_value')
                            ->label('Amount (cents)')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('status')
                            ->label('Status')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('attempts_count')
                            ->label('Attempts')
                            ->disabled(),
                    ])->columns(2),

                \Filament\Forms\Components\Section::make('Payment Details')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('card_brand')
                            ->label('Card Brand')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('card_last4')
                            ->label('Card Last 4')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('authorization_code')
                            ->label('Authorization Code')
                            ->disabled(),
                    ])->columns(2),

                \Filament\Forms\Components\Section::make('Risk & Security')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('risk_level')
                            ->label('Risk Level')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('risk_score')
                            ->label('Risk Score')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('network_status')
                            ->label('Network Status')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('seller_message')
                            ->label('Seller Message')
                            ->disabled(),
                    ])->columns(2),

                \Filament\Forms\Components\Section::make('Additional Information')
                    ->schema([
                        \Filament\Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->disabled()
                            ->rows(3),
                        \Filament\Forms\Components\TextInput::make('receipt_url')
                            ->label('Receipt URL')
                            ->disabled(),
                        \Filament\Forms\Components\DateTimePicker::make('stripe_created_at')
                            ->label('Stripe Created At')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('charge_id')
                    ->label('Charge ID')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('customer_id')
                    ->label('Customer ID')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tenant_name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ?: 'N/A'),

                TextColumn::make('amount_value')
                    ->label('Amount')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn ($state) => '$'.number_format($state / 100, 2)),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'refunded' => 'gray',
                        'disputed' => 'warning',
                        'succeeded' => 'success',
                        'failed' => 'danger',
                        'pending' => 'info',
                        'posted' => 'success',
                        'void' => 'gray',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (?string $state): string => Str::ucfirst($state)),

                TextColumn::make('card_brand')
                    ->label('Card')
                    ->searchable()
                    ->formatStateUsing(function ($state, $record) {
                        if (! $state) {
                            return 'N/A';
                        }

                        $brand = ucfirst(strtolower($state));
                        $svgPath = asset('images/card-providers/'.strtolower($state).'.svg');
                        $last4 = $record->card_last4;

                        return <<<HTML
                                <div class="flex items-center text-xs text-gray-700">
                                    <img src="{$svgPath}" alt="{$brand}" class="w-5 h-5">
                                    <span class="whitespace-nowrap italic">
                                        &nbsp;
                                        <span class="relative -top-0.5 text-[0.65rem] align-top">****</span>{$last4}
                                    </span>
                                </div>
                            HTML;
                    })
                    ->html(),

                TextColumn::make('authorization_code')
                    ->label('Auth Code')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('attempts_count')
                    ->label('Attempts')
                    ->numeric(),

                TextColumn::make('risk_level')
                    ->label('Risk Level')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'normal' => 'success',
                        'elevated' => 'warning',
                        'highest' => 'danger',
                        default => 'secondary',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('risk_score')
                    ->label('Risk Score')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('network_status')
                    ->label('Network Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'approved_by_network' => 'success',
                        'declined_by_network' => 'danger',
                        'pending' => 'warning',
                        default => 'secondary',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('receipt_url')
                    ->label('Receipt')
                    ->url(fn ($state) => $state)
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-receipt-refund')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('stripe_created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'succeeded' => 'Succeeded',
                        'failed' => 'Failed',
                        'pending' => 'Pending',
                        'posted' => 'Posted',
                        'void' => 'Void',
                    ]),
                Tables\Filters\SelectFilter::make('risk_level')
                    ->options([
                        'normal' => 'Normal',
                        'elevated' => 'Elevated',
                        'highest' => 'Highest',
                    ]),
                Tables\Filters\SelectFilter::make('tenant')
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('retry_attempts')
                    ->label('Retry Attempts')
                    ->queries(
                        true: fn ($query) => $query->whereRaw('(SELECT COUNT(*) FROM stripe_transactions t2 WHERE t2.payment_intent_id = stripe_transactions.payment_intent_id) > 1'),
                        false: fn ($query) => $query->whereRaw('(SELECT COUNT(*) FROM stripe_transactions t2 WHERE t2.payment_intent_id = stripe_transactions.payment_intent_id) = 1'),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->defaultSort('stripe_created_at', 'desc');
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'view' => Pages\ViewTransaction::route('/{record}'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
