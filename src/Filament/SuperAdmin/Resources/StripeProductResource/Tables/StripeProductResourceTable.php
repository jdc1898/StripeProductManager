<?php

namespace App\Filament\SuperAdmin\Resources\StripeProductResource\Tables;

use App\Filament\SuperAdmin\Resources\StripePriceResource;
// use App\Services\Redbird\Redbird;
use App\Filament\SuperAdmin\Resources\StripeProductResource\Pages;
use App\Models\StripeProduct;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class StripeProductResourceTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->heading('Stripe Products')
            ->description('Products synced from Stripe. These represent the actual products in your Stripe account.')
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->persistSearchInSession()
            ->modifyQueryUsing(function (Builder $query, $livewire) {
                // Handle filtering based on tab
                if ($livewire instanceof Pages\ListProducts) {
                    if ($livewire->activeTab === 'inactive') {
                        $query->where('active', false);
                    } elseif ($livewire->activeTab === 'active') {
                        $query->where('active', true);
                    }
                }

                return $query;
            })
            ->columns([
                TextColumn::make('name')
                    ->weight('bold')
                    ->size('lg')
                    ->description(function (StripeProduct $record) {
                        $description = '';

                        // Add a badge that shows the default price
                        if ($defaultPrice = $record->defaultStripePrice) {
                            $description .= view('components.filament.badge-description', [
                                'price' => $defaultPrice->formatForDisplay(),
                                'currency' => Str::upper($defaultPrice->currency),
                                'units' => $defaultPrice->metadata['package_units'] ?? null,
                                'billing_scheme' => $defaultPrice->billing_scheme ?? null,
                                'tiers' => [
                                    'starting_amount' => null,
                                    'flat_amount' => null,
                                ],
                                'period' => isset($defaultPrice->recurring['interval']) ? $defaultPrice->recurring['interval'] : null,
                            ])->render();
                        }

                        return new HtmlString($description);
                    })
                    ->searchable(),

                TextColumn::make('stripe_id')
                    ->label('Stripe ID')
                    ->copyable()
                    ->searchable()
                    ->visible(fn ($livewire) => $livewire instanceof Pages\ListProducts && $livewire->activeTab === 'all'),

                TextColumn::make('active')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->icon(fn ($state) => $state ? 'heroicon-o-eye' : 'heroicon-o-eye-slash')
                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive')
                    ->visible(fn ($livewire) => $livewire instanceof Pages\ListProducts && $livewire->activeTab === 'all'),

                TextColumn::make('stripePrices_count')
                    ->label('Prices')
                    ->getStateUsing(function (StripeProduct $record): int {
                        return \App\Models\StripePrice::where('product', $record->stripe_id)->count();
                    })
                    ->badge()
                    ->color('success')
                    ->url(function (StripeProduct $record): string {
                        return StripePriceResource::getUrl('index', [
                            'tableFilters[product][value]' => $record->stripe_id,
                        ]);
                    })
                    ->placeholder('-')
                    ->weight('semibold')
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('created')
                    ->label('Created in Stripe')
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return 'N/A';
                        }

                        $date = \Carbon\Carbon::createFromTimestamp($state);

                        // If within last 30 days, show human diff
                        if ($date->diffInDays(now()) <= 30) {
                            return $date->diffForHumans();
                        }

                        // Otherwise show formatted date
                        return $date->format('M j, Y g:i A');
                    })
                    ->sortable()
                    ->alignRight()
                    ->visible(fn ($livewire) => $livewire instanceof Pages\ListProducts && $livewire->activeTab !== 'inactive'),

                TextColumn::make('updated')
                    ->label('Updated in Stripe')
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return 'N/A';
                        }

                        $date = \Carbon\Carbon::createFromTimestamp($state);

                        // If within last 30 days, show human diff
                        if ($date->diffInDays(now()) <= 30) {
                            return $date->diffForHumans();
                        }

                        // Otherwise show formatted date
                        return $date->format('M j, Y g:i A');
                    })
                    ->sortable()
                    ->alignRight()
                    ->visible(fn ($livewire) => $livewire instanceof Pages\ListProducts && $livewire->activeTab !== 'inactive'),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('activate')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->label('Activate')
                        ->requiresConfirmation()
                        ->modalHeading('Activate Product')
                        ->modalDescription('This will make the product available in Stripe. Are you sure you want to continue?')
                        ->modalSubmitActionLabel('Yes, activate product')
                        ->visible(fn ($record, $livewire) => ! $record->active && $livewire->activeTab === 'all')
                        ->action(function (StripeProduct $record) {
                            try {
                                // Update in Stripe
                                // Redbird::stripe()->products->update($record->stripe_id, ['active' => true]);

                                // Update local record
                                $record->active = true;
                                $record->save();

                                Notification::make()
                                    ->title('Product activated')
                                    ->body('The product is now active in Stripe.')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error activating product')
                                    ->body('There was an error activating the product: '.$e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('deactivate')
                        ->icon('heroicon-m-arrow-down-circle')
                        ->color('danger')
                        ->label('Deactivate')
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate Product')
                        ->modalDescription('This will deactivate the product in Stripe. Are you sure you want to continue?')
                        ->visible(fn ($record, $livewire) => ($livewire->activeTab === 'all' || $livewire->activeTab === 'active') && $record->active)
                        ->action(function (StripeProduct $record) {
                            try {
                                // Update in Stripe
                                // Redbird::stripe()->products->update($record->stripe_id, ['active' => false]);

                                // Update local record
                                $record->active = false;
                                $record->save();

                                Notification::make()
                                    ->title('Product deactivated')
                                    ->body('The product has been deactivated in Stripe.')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Failed to deactivate product')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('refresh')
                        ->icon('heroicon-m-arrow-path')
                        ->color('gray')
                        ->label('Refresh from Stripe')
                        ->tooltip('Fetch latest data from Stripe')
                        ->action(function (StripeProduct $record) {
                            try {
                                // Fetch fresh data from Stripe
                                // $stripeProduct = Redbird::stripe()->products->retrieve($record->stripe_id);

                                // Update local record with fresh data
                                $record->update([
                                    'updated' => now()->timestamp,
                                ]);

                                Notification::make()
                                    ->title('Product refreshed')
                                    ->body('The product data has been updated from Stripe.')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Refresh failed')
                                    ->body('Failed to refresh product: '.$e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->successRedirectUrl(fn (): string => request()->url()),

                    ViewAction::make()
                        ->icon('heroicon-m-eye')
                        ->visible(fn ($livewire) => $livewire->activeTab === 'all'),

                    DeleteAction::make()
                        ->icon('heroicon-m-trash')
                        ->visible(fn ($livewire) => $livewire->activeTab === 'all')
                        ->before(function (StripeProduct $record) {
                            // Archive the product in Stripe
                            try {
                                // Redbird::stripe()->products->update($record->stripe_id, ['active' => false]);

                                Notification::make()
                                    ->title('Product archived in Stripe')
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
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->dropdown(true)
                    ->dropdownPlacement('bottom-start'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Fetch from Stripe')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->action(function () {
                        try {
                            // Run the command to fetch products from Stripe
                            Artisan::call('stripe:fetch-products', ['--save' => true]);

                            Notification::make()
                                ->title('Products fetched from Stripe')
                                ->body('All products have been fetched and saved to the database.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Failed to fetch products')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->successNotificationTitle('Products fetched successfully from Stripe.'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
