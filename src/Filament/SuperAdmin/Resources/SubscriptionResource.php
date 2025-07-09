<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\SubscriptionResource\Pages;
use DateTime;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Laravel\Cashier\Subscription;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $modelLabel = 'Subscription';

    protected static ?string $navigationGroup = 'Revenue';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull()
                    ->helperText('Adding a subscription to a user will create a "locally managed" subscription, which means the user will be able to use subscription features without being billed, and they can later convert to a "payment provider managed" subscription from their dashboard.'),

                Select::make('product_id')
                    ->label('Plan')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),

                DateTimePicker::make('trial_ends_at')
                    ->label('Trial Ends At')
                    ->default(fn (?string $state): ?DateTime => $state ? new DateTime($state) : null)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->slideOver(),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('user.tenant.name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('stripePrice.stripeProduct.name')
                    ->label('Stripe Product')
                    ->description(function ($record) {
                        if (! $record->stripePrice) {
                            return null;
                        }

                        return new HtmlString(view('components.filament.badge-description', [
                            'price' => $record->stripePrice->formatForDisplay(),
                            'currency' => strtoupper($record->stripePrice->currency),
                            'units' => $record->stripePrice->metadata['package_units'] ?? null,
                            'billing_scheme' => $record->stripePrice->billing_scheme ?? null,
                            'tiers' => [
                                'starting_amount' => isset($record->stripePrice->tiers[0]['unit_amount']) ? $record->stripePrice->tiers[0]['unit_amount'] : null,
                                'flat_amount' => isset($record->stripePrice->tiers[0]['flat_amount']) ? $record->stripePrice->tiers[0]['flat_amount'] : null,
                            ],
                            'period' => isset($record->stripePrice->recurring['interval']) ? $record->stripePrice->recurring['interval'] : null,
                        ])->render());
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable(),

                Tables\Columns\TextColumn::make('plan.unit_amount')
                    ->label('Price')
                    ->money('usd', divideBy: 100)
                    ->sortable(),

                Tables\Columns\TextColumn::make('stripe_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'trialing' => 'info',
                        'incomplete' => 'info',
                        'incomplete_expired' => 'warning',
                        'active' => 'success',
                        'unpaid' => 'danger',
                        'past_due' => 'warning',
                        'paused' => 'info',
                        'canceled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(function (?string $state): string {
                        return Str::ucfirst(str_replace('_', ' ', $state));
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListSubscriptions::route('/'),
            // 'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
