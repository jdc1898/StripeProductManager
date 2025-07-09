<?php

namespace App\Filament\SuperAdmin\Resources\StripeProductResource\RelationManagers;


use App\Filament\Forms\PriceForm;
use App\Models\StripePrice;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;

class PriceRelationManager extends RelationManager
{
    protected static string $relationship = 'stripePrices';

    protected static ?string $inverseRelationship = 'stripeProduct';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form->schema(PriceForm::getForm(
            $this->getOwnerRecord()->stripe_id,
        ));
    }

    protected function handleRecordCreation(array $data): Model
    {
        // For now, return a placeholder since we're not creating prices directly
        // This would need to be implemented based on your requirements
        return new StripePrice();
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // For now, return the record as-is since we're not updating prices directly
        // This would need to be implemented based on your requirements
        return $record;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Available Prices')
            ->columns([
                TextColumn::make('nickname')
                    ->description(function (StripePrice $record) {

                        $description = '$'.number_format($record->unit_amount / 100, 2);

                        /**
                         * Show the price and the billing interval
                         */
                        if ($record->recurring) {
                            $description .= ' Per '.($record->recurring['interval'] ?? 'period');
                        }

                        if ($record->metadata['package_units'] ?? false) {
                            $units = $record->metadata['package_units'];
                            $label = $record->stripeProduct?->unit_label ?? 'units';
                            $description .= $units.' '.($units == 1 ? Str::singular($label) : Str::plural($label));
                        }

                        if ($record->billing_scheme === 'tiered') {
                            $description .= ' Tiered pricing';
                        }

                        return new HtmlString($description);
                    })
                    ->searchable(),

                TextColumn::make('stripe_id')
                    ->label('Stripe ID')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('active')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive'),

                TextColumn::make('created')
                    ->label('Created')
                    ->formatStateUsing(function ($state) {
                        if (!$state) {
                            return 'N/A';
                        }
                        return \Carbon\Carbon::createFromTimestamp($state)->format('M j, Y g:i A');
                    })
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                // Tables\Actions\EditAction::make()->slideOver(),
                Tables\Actions\Action::make('activate')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->label('Activate')
                    ->requiresConfirmation()
                    ->modalHeading('Activate Price')
                    ->modalDescription('This will make the price available for purchase. Are you sure you want to continue?')
                    ->modalSubmitActionLabel('Yes, activate price')
                    ->visible(fn ($record) => !$record->active)
                    ->action(function ($record) {
                        try {
                            // Update in Stripe
                            //\App\Services\Redbird\Redbird::stripe()->prices->update($record->stripe_id, ['active' => true]);

                            // Update local record
                            $record->active = true;
                            $record->save();

                            Notification::make()
                                ->title('Price activated')
                                ->body('The price is now available for purchase.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error activating price')
                                ->body('There was an error activating the price: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('deactivate')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->label('Deactivate')
                    ->requiresConfirmation()
                    ->modalHeading('Deactivate Price')
                    ->modalDescription('This will make the price unavailable for purchase. Are you sure you want to continue?')
                    ->modalSubmitActionLabel('Yes, deactivate price')
                    ->visible(fn ($record) => $record->active)
                    ->action(function ($record) {
                        try {
                            // Update in Stripe
                            //\App\Services\Redbird\Redbird::stripe()->prices->update($record->stripe_id, ['active' => false]);

                            // Update local record
                            $record->active = false;
                            $record->save();

                            Notification::make()
                                ->title('Price deactivated')
                                ->body('The price is no longer available for purchase.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error deactivating price')
                                ->body('There was an error deactivating the price: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->before(function ($record) {
                        try {
                            // Archive in Stripe
                            //\App\Services\Redbird\Redbird::stripe()->prices->update($record->stripe_id, ['active' => false]);

                            Notification::make()
                                ->title('Price archived in Stripe')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Failed to archive in Stripe')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('fetch_from_stripe')
                    ->label('Fetch from Stripe')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->action(function () {
                        try {
                            // Run the command to fetch prices from Stripe
                            Artisan::call('stripe:fetch-prices', ['--save' => true]);

                            Notification::make()
                                ->title('Prices fetched from Stripe')
                                ->body('All prices have been fetched and saved to the database.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Failed to fetch prices')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->successNotificationTitle('Prices fetched successfully from Stripe.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }
}
