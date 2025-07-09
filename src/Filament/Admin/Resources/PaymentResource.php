<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PaymentResource\Pages;
use App\Models\StripeTransaction;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PaymentResource extends Resource
{
    protected static ?string $model = StripeTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-s-credit-card';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Payments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('amount')
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
                        'success' => 'success',
                        'failed' => 'danger',
                        'pending' => 'info',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (?string $state): string => Str::ucfirst($state)),

                TextColumn::make('payment_method_details_card_brand')
                    ->label('Payment Method')
                    ->searchable()
                    ->formatStateUsing(function ($state, $record) {
                        $brand = ucfirst(strtolower($state));
                        $svgPath = asset('images/card-providers/'.strtolower($state).'.svg');
                        $last4 = $record->payment_method_details_card_last4;

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

                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
