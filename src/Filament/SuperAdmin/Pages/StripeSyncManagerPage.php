<?php

namespace App\Filament\SuperAdmin\Pages;

use App\Models\StripeProduct;
use App\Models\StripeSyncLog;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StripeSyncManagerPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Stripe Sync';

    protected static ?string $title = 'Stripe Sync Manager';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.super-admin.pages.stripe-sync-manager-page';

    public function table(Table $table): Table
    {
        return $table
            ->query(StripeSyncLog::query()->with('syncable')->latest())
            ->columns([
                TextColumn::make('syncable.name')
                    ->label('Item')
                    ->description(
                        fn (?StripeSyncLog $record): ?string => $record?->syncable_type ? class_basename($record->syncable_type) : null
                    )
                    ->placeholder('N/A')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('action')
                    ->badge()
                    ->color('blue')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        default => 'warning',
                    })
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime()
                    ->sortable()
                    ->alignRight()
                    ->toggleable(),
                TextColumn::make('error_message')
                    ->label('Error')
                    ->color('danger')
                    ->visible(
                        fn (?StripeSyncLog $record): bool => $record?->status === 'failed'
                    )
                    ->wrap()
                    ->toggleable()
                    ->placeholder('No error message'),
                TextColumn::make('stripe_id')
                    ->label('Stripe ID')
                    ->visible(
                        fn (?StripeSyncLog $record): bool => $record?->status === 'success'
                    )
                    ->toggleable()
                    ->placeholder('N/A'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'success' => 'Successful',
                        'failed' => 'Failed',
                    ]),
                SelectFilter::make('syncable_type')
                    ->label('Item Type')
                    ->options([
                        StripeProduct::class => 'Product',
                        \App\Models\StripePrice::class => 'Price',
                    ]),
            ])
            ->actions([
                Action::make('retry')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(
                        fn (?StripeSyncLog $record): bool => $record?->status === 'failed' &&
                        $record?->syncable instanceof StripeProduct
                    )
                    ->action(function (StripeSyncLog $record): void {
                        if (! $record->syncable instanceof StripeProduct) {
                            Notification::make()
                                ->title('Cannot Retry Sync')
                                ->body('This sync log is not associated with a product.')
                                ->danger()
                                ->send();

                            return;
                        }

                        try {
                            $record->syncable->triggerStripeSync();

                            Notification::make()
                                ->title('Sync Retry Initiated')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Failed to Retry Sync')
                                ->body('An error occurred while trying to retry the sync.')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->poll('30s');
    }
}
