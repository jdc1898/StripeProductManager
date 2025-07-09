<?php

namespace App\Filament\SuperAdmin\Resources;

use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\SuperAdmin\Resources\OrderResource\Pages;
use App\Models\StripeInvoice;
use Illuminate\Support\Str;

class OrderResource extends Resource
{
    protected static ?string $model = StripeInvoice::class;

    protected static ?string $modelLabel = 'Invoice';

    protected static ?string $navigationGroup = 'Revenue';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Section::make('Invoice Details')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('stripe_id')
                            ->label('Invoice ID')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('number')
                            ->label('Invoice Number')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('status')
                            ->label('Status')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('billing_reason')
                            ->label('Billing Reason')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('collection_method')
                            ->label('Collection Method')
                            ->disabled(),
                    ])->columns(2),

                \Filament\Forms\Components\Section::make('Customer Information')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('customer')
                            ->label('Customer ID')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('customer_name')
                            ->label('Customer Name')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('customer_email')
                            ->label('Customer Email')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('customer_phone')
                            ->label('Customer Phone')
                            ->disabled(),
                    ])->columns(2),

                \Filament\Forms\Components\Section::make('Amount Details')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('amount_due')
                            ->label('Amount Due (cents)')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('amount_paid')
                            ->label('Amount Paid (cents)')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('amount_remaining')
                            ->label('Amount Remaining (cents)')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('total')
                            ->label('Total (cents)')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('currency')
                            ->label('Currency')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('attempt_count')
                            ->label('Attempt Count')
                            ->disabled(),
                    ])->columns(2),

                \Filament\Forms\Components\Section::make('Additional Information')
                    ->schema([
                        \Filament\Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->disabled()
                            ->rows(3),
                        \Filament\Forms\Components\TextInput::make('hosted_invoice_url')
                            ->label('Hosted Invoice URL')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('invoice_pdf')
                            ->label('Invoice PDF')
                            ->disabled(),
                        \Filament\Forms\Components\DateTimePicker::make('stripe_created_at')
                            ->label('Created At')
                            ->disabled(),
                        \Filament\Forms\Components\DateTimePicker::make('due_date')
                            ->label('Due Date')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('stripe_id')
                    ->label('Invoice ID')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('number')
                    ->label('Invoice #')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ?: 'N/A'),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => '$'.number_format($state / 100, 2)),

                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Paid')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => '$'.number_format($state / 100, 2)),

                Tables\Columns\TextColumn::make('amount_remaining')
                    ->label('Remaining')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => '$'.number_format($state / 100, 2)),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'paid' => 'success',
                        'open' => 'warning',
                        'draft' => 'gray',
                        'void' => 'danger',
                        'uncollectible' => 'danger',
                        'deleted' => 'gray',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (?string $state): string => Str::ucfirst($state)),

                Tables\Columns\TextColumn::make('billing_reason')
                    ->label('Billing Reason')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::ucfirst(str_replace('_', ' ', $state)))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('collection_method')
                    ->label('Collection Method')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::ucfirst($state))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('attempt_count')
                    ->label('Attempts')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('stripe_created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'paid' => 'Paid',
                        'open' => 'Open',
                        'draft' => 'Draft',
                        'void' => 'Void',
                        'uncollectible' => 'Uncollectible',
                        'deleted' => 'Deleted',
                    ]),
                Tables\Filters\SelectFilter::make('billing_reason')
                    ->options([
                        'subscription_cycle' => 'Subscription Cycle',
                        'subscription_create' => 'Subscription Create',
                        'subscription_update' => 'Subscription Update',
                        'subscription_threshold' => 'Subscription Threshold',
                        'manual' => 'Manual',
                    ]),
                Tables\Filters\SelectFilter::make('collection_method')
                    ->options([
                        'charge_automatically' => 'Charge Automatically',
                        'send_invoice' => 'Send Invoice',
                    ]),
                Tables\Filters\TernaryFilter::make('paid')
                    ->label('Payment Status')
                    ->queries(
                        true: fn ($query) => $query->where('paid', true),
                        false: fn ($query) => $query->where('paid', false),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
