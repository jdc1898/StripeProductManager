<?php

namespace App\Filament\SuperAdmin\Forms;

use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Forms\Components\RadioCard;
use App\Forms\Components\SelectCard;
use App\Forms\Components\TieredPricing;
use App\Models\StripeProduct;
use Illuminate\Support\Str;

class PriceForm
{
    public static function getForm(int $productId): array
    {
        return [
            Actions::make([
                Action::make('fill_with_flat_factory_data')
                    ->label('Flat')
                    ->color('warning')
                    ->icon('heroicon-m-beaker')
                    ->extraAttributes(['data-dusk' => 'fill-with-flat-rate-factory-data'])
                    ->action(function (Set $set) {

                        if (app()->environment(['local', 'dusk'])) {
                            $testProduct = StripeProduct::factory()->make()->toArray();
                            $set('nickname', $testProduct['name'].' Lite');
                            $set('active', false);
                            $set('metadata', $testProduct['metadata']);
                            $set('images', $testProduct['images']);
                            $set('marketing_features', $testProduct['marketing_features']);
                            $set('package_dimensions', $testProduct['package_dimensions']);
                            $set('statement_descriptor', $testProduct['statement_descriptor']);
                            $set('unit_label', $testProduct['unit_label']);
                            $set('url', $testProduct['url']);
                            $set('slug', Str::slug($testProduct['name']));
                            $set('is_synced', false);

                            // Non-product fields used to generate the default price.
                            $set('amount', rand(100, 9999));
                            $set('billing_period', 'month');
                            $set('tax_behavior', 'exclusive');
                            $set('lookup_key', Str::slug($testProduct['name']));
                        }
                    })
                    ->button(),

                Action::make('package_fill_with_factory_data')
                    ->label('Package')
                    ->icon('heroicon-m-beaker')
                    ->color('warning')
                    ->extraAttributes(['data-dusk' => 'fill-with-package-factory-data'])
                    ->action(function (Set $set) {

                        if (app()->environment(['local', 'dusk'])) {
                            $testProduct = StripeProduct::factory()->make()->toArray();

                            $set('nickname', $testProduct['name'].' Lite');
                            $set('active', false);
                            $set('metadata', $testProduct['metadata']);
                            $set('images', $testProduct['images']);
                            $set('marketing_features', $testProduct['marketing_features']);
                            $set('package_dimensions', $testProduct['package_dimensions']);
                            $set('shippable', $testProduct['shippable']);
                            $set('statement_descriptor', $testProduct['statement_descriptor']);
                            $set('unit_label', $testProduct['unit_label']);
                            $set('url', $testProduct['url']);
                            $set('slug', Str::slug($testProduct['name']));
                            $set('is_synced', false);

                            // Non-product fields used to generate the default price.
                            $set('recurring_pricing_model', 'package');
                            $set('package_units', rand(1, 100));

                            $set('amount', rand(100, 9999));
                            $set('billing_period', 'month');
                            $set('tax_behavior', 'exclusive');
                            $set('lookup_key', Str::slug($testProduct['name']));
                        }
                    })
                    ->button(),

                Action::make('tier_fill_with_factory_data')
                    ->label('Tier')
                    ->color('warning')
                    ->icon('heroicon-m-beaker')
                    ->extraAttributes(['data-dusk' => 'fill-with-tiered-factory-data'])
                    ->action(function (Set $set) {

                        if (app()->environment(['local', 'dusk'])) {

                            $testProduct = StripeProduct::factory()->make()->toArray();

                            $stop = rand(100, 5000);
                            $price = rand(100, 500);

                            // Simulate a tiered pricing model
                            $set('nickname', $testProduct['name'].' Lite');
                            $set('active', false);
                            $set('metadata', $testProduct['metadata']);
                            $set('images', $testProduct['images']);
                            $set('marketing_features', $testProduct['marketing_features']);
                            $set('package_dimensions', $testProduct['package_dimensions']);
                            $set('shippable', $testProduct['shippable']);
                            $set('statement_descriptor', $testProduct['statement_descriptor']);
                            $set('unit_label', $testProduct['unit_label']);
                            $set('url', $testProduct['url']);
                            $set('slug', Str::slug($testProduct['name']));
                            $set('is_synced', false);

                            // Non-product fields used to generate the default price.
                            $set('recurring_pricing_model', 'tiered');
                            $set('tier_type', 'volume');
                            $set('tiers', [
                                ['first_unit' => 1, 'last_unit' => $stop++, 'per_unit' => 0, 'flat_fee' => $price],
                                ['first_unit' => $stop++, 'last_unit' => rand($stop++, 9999), 'per_unit' => 0, 'flat_fee' => $price - 10],
                            ]);
                            $set('package_units', rand(1, 100));

                            $set('amount', rand(100, 9999));
                            $set('billing_period', 'month');
                            $set('tax_behavior', 'exclusive');
                            $set('lookup_key', Str::slug($testProduct['name']));
                        }
                    })
                    ->button(),

                Action::make('usage_fill_with_factory_data')
                    ->label('Usage')
                    ->color('warning')
                    ->icon('heroicon-m-beaker')
                    ->extraAttributes(['data-dusk' => 'fill-with-usage-factory-data'])
                    ->action(function (Set $set) {

                        if (app()->environment(['local', 'dusk'])) {
                            $testProduct = StripeProduct::factory()->make()->toArray();

                            // Simulate a tiered pricing model
                            $set('recurring_pricing_model', 'tiered');
                            $set('tier_type', 'volume');
                            $set('tiers', [
                                ['first_unit' => 1, 'last_unit' => 10, 'per_unit' => 0, 'flat_fee' => 100.00],
                                ['first_unit' => 11, 'last_unit' => 20, 'per_unit' => 0, 'flat_fee' => 90.00],
                                ['first_unit' => 21, 'last_unit' => null, 'per_unit' => 0, 'flat_fee' => 80.00],
                            ]);

                            $set('name', $testProduct['name']);
                            $set('billing_type', 'recurring');
                            // $set('recurring_pricing_model', 'flat-rate');
                            $set('amount', 1000);
                            $set('billing_period', 'month');
                            $set('tax_behavior', 'exclusive');

                            $set('lookup_key', 'default-plan');
                        }
                    })
                    ->button(),

            ])->columnSpanFull(),

            Select::make('product_id')
                ->label('Product')
                ->options(fn () => StripeProduct::query()
                    ->where('active', true)
                    ->whereNull('deleted_at')
                    ->pluck('name', 'id'))
                ->required()
                ->searchable()
                ->preload()
                ->live()
                ->columnSpanFull()
                ->helperText('Select the product this price will be associated with.')
                ->extraAttributes(['data-dusk' => 'product-id']),

            TextInput::make('nickname')
                ->label('Name')
                ->required()
                ->helperText('Name of the price, visible to customers.')
                ->maxLength(255)
                ->columnSpanFull()
                ->extraInputAttributes(['data-dusk' => 'nickname']),

            RadioCard::make('type')
                ->options([
                    'recurring' => 'Recurring',
                    'one-time' => 'One-off',
                ])
                ->descriptions([
                    'recurring' => 'Charge an ongoing fee',
                    'one-time' => 'Charge a one-time fee',
                ])
                ->required()
                ->live()
                ->default('recurring')
                ->columnSpanFull()
                ->duskSelector('billing-type'),

            SelectCard::make('one_off_pricing_model')
                ->label('Pricing Model')
                ->columnSpanFull()
                ->options([
                    'flat-rate' => [
                        'title' => 'Flat rate',
                        'description' => 'Offer a fixed price for a single unit or package',
                    ],
                    'package' => [
                        'title' => 'Package Pricing',
                        'description' => 'Price by package, bundle, or group of units',
                    ],
                    'customer-defined' => [
                        'title' => 'Customer chooses price',
                        'description' => 'You or your customer defines the price at the point of sale',
                    ],
                ])
                ->visible(fn (Get $get) => $get('type') === 'one-time')
                ->default('flat-rate')
                ->duskSelector('one-off-pricing-model')
                ->live()
                ->afterStateUpdated(function (Get $get, Set $set) {
                    $set('billing_scheme', 'per_unit');
                })
                ->dehydrated(false),

            SelectCard::make('recurring_pricing_model')
                ->label('Pricing Model')
                ->columnSpanFull()
                ->options([
                    'flat-rate' => [
                        'title' => 'Flat rate',
                        'description' => 'Charge the same price for all customers',
                    ],
                    'package' => [
                        'title' => 'Package',
                        'description' => 'Price by package, bundle, or group of units',
                    ],
                    'tiered' => [
                        'title' => 'Tiered pricing',
                        'description' => 'Price varies based on quantity purchased',
                    ],
                    'usage-based' => [
                        'title' => 'Usage-based',
                        'description' => 'Charge based on usage or consumption',
                    ],
                ])
                ->visible(fn (Get $get) => $get('type') === 'recurring')
                ->default('flat-rate')
                ->duskSelector('recurring-pricing-model')
                ->live()
                ->afterStateUpdated(function (Get $get, Set $set) {
                    $model = $get('recurring_pricing_model');
                    if ($model === 'tiered' || $model === 'usage-based') {
                        $set('billing_scheme', 'tiered');
                    } else {
                        $set('billing_scheme', 'per_unit');
                    }
                })
                ->dehydrated(false),

            Checkbox::make('has_suggested_amount')
                ->label('Suggest a preset amount')
                ->columnSpanFull()
                ->visible(
                    fn (Get $get): bool => $get('type') === 'one-time' &&
                        $get('one_off_pricing_model') === 'customer-defined'
                )
                ->extraAttributes(['data-dusk' => 'has-suggested-amount'])
                ->live(),

            TextInput::make('suggested_amount')
                ->label('Suggested Amount')
                ->prefix('$')
                ->placeholder('0.00')
                ->visible(
                    fn (Get $get): bool => $get('has_suggested_amount') === true
                )
                ->extraInputAttributes(['data-dusk' => 'suggested-amount']),

            Checkbox::make('has_limits')
                ->label('Set limits')
                ->visible(
                    fn (Get $get): bool => $get('type') === 'one-time' &&
                        $get('one_off_pricing_model') === 'customer-defined'
                )
                ->extraAttributes(['data-dusk' => 'has-limits'])
                ->live(),

            Grid::make(2)
                ->schema([
                    TextInput::make('minimum_amount')
                        ->label('Minimum amount')
                        ->prefix('$')
                        ->placeholder('0.00')
                        ->extraInputAttributes(['data-dusk' => 'minimum-amount']),
                    TextInput::make('maximum_amount')
                        ->label('Maximum amount')
                        ->prefix('$')
                        ->placeholder('0.00')
                        ->extraInputAttributes(['data-dusk' => 'maximum-amount']),
                ])
                ->visible(
                    fn (Get $get): bool => $get('has_limits') === true
                ),

            SelectCard::make('tier_type')
                ->hiddenLabel()
                ->columnSpanFull()
                ->duskSelector('tier-type')
                ->options([
                    'volume' => [
                        'title' => 'Volume',
                        'description' => 'Apply the same unit price to all units based on the total quantity',
                    ],
                    'graduated' => [
                        'title' => 'Graduated',
                        'description' => 'Apply different unit prices to different quantity ranges',
                    ],
                ])
                ->default('volume')
                ->live()
                ->visible(
                    fn (Get $get): bool => $get('type') === 'recurring' &&
                        $get('recurring_pricing_model') === 'tiered'
                ),

            TieredPricing::make('tiers')
                ->columnSpanFull()
                ->extraAttributes(['data-dusk' => 'tiers'])
                ->live()
                ->afterStateUpdated(function (Get $get, Set $set) {
                    $set('billing_scheme', 'tiered');
                })
                ->visible(
                    fn (Get $get): bool => $get('type') === 'recurring' && $get('recurring_pricing_model') === 'tiered' ||
                        $get('type') === 'recurring' && $get('recurring_pricing_model') === 'usage-based' && $get('usage_based_type') === 'tier'
                ),

            SelectCard::make('usage_based_type')
                ->hiddenLabel()
                ->columnSpanFull()
                ->duskSelector('usage-based-type')
                ->options([
                    'unit' => [
                        'title' => 'Per unit',
                        'description' => 'Price by number of users, units, or seats.',
                    ],
                    'package' => [
                        'title' => 'Per package',
                        'description' => 'Price by package, byndle, or group of units.',
                    ],
                    'tier' => [
                        'title' => 'Per tier',
                        'description' => 'Price based on quantity.',
                    ],
                ])
                ->default('unit')
                ->live()
                ->visible(
                    fn (Get $get): bool => $get('type') === 'recurring' &&
                        $get('recurring_pricing_model') === 'usage-based'
                ),

            TextInput::make('amount')
                ->label('Amount')
                ->required()
                ->numeric()
                ->minValue(0)
                ->prefix('$')
                ->suffix('USD')
                ->helperText('The amount to charge for this product. This is used to generate the default price. This should be in cents.')
                ->columnSpanFull()
                ->hidden(
                    fn (Get $get): bool => ($get('type') === 'recurring' && $get('recurring_pricing_model') === 'tiered') ||
                        ($get('type') === 'one-time' && $get('one_off_pricing_model') === 'customer-defined')
                )
                ->extraInputAttributes(['data-dusk' => 'amount']),

            TextInput::make('package_units')
                ->hiddenLabel()
                ->prefix('per')
                ->suffix('units')
                ->columnSpanFull()
                ->numeric()
                ->minValue(1)
                ->placeholder('10')
                ->extraInputAttributes(['data-dusk' => 'package-units'])
                ->visible(function (Get $get) {
                    return $get('type') === 'recurring' && $get('recurring_pricing_model') === 'package' ||
                        $get('type') === 'recurring' && $get('recurring_pricing_model') === 'usage-based' && $get('usage_based_type') === 'package' ||
                        $get('type') === 'one-time' && $get('one_off_pricing_model') === 'package';
                }),

            SelectCard::make('billing_period')
                ->label('Billing Period')
                ->columnSpanFull()
                ->options([
                    'day' => [
                        'title' => 'Daily',
                        'description' => 'Charge every day',
                    ],
                    'week' => [
                        'title' => 'Weekly',
                        'description' => 'Charge every week',
                    ],
                    'month' => [
                        'title' => 'Monthly',
                        'description' => 'Charge every month',
                    ],
                    'year' => [
                        'title' => 'Yearly',
                        'description' => 'Charge every year',
                    ],
                    '3months' => [
                        'title' => 'Every 3 months',
                        'description' => 'Charge every quarter',
                    ],
                    '6months' => [
                        'title' => 'Every 6 months',
                        'description' => 'Charge twice a year',
                    ],
                ])
                ->visible(fn (Get $get) => $get('type') === 'recurring')
                ->default('month')
                ->live()
                ->duskSelector('billing-period'),

            SelectCard::make('tax_behavior')
                ->label('Include tax in price')
                ->columnSpanFull()
                ->options([
                    'inclusive' => [
                        'title' => 'Yes',
                        'description' => 'Tax is included in the price',
                    ],
                    'exclusive' => [
                        'title' => 'No',
                        'description' => 'Tax is added to the price',
                    ],
                ])
                ->default('exclusive')
                ->live()
                ->duskSelector('tax-behavior'),

            Hidden::make('billing_scheme')
                ->default('per_unit')
                ->live()
                ->afterStateUpdated(function (Get $get, Set $set) {
                    $model = $get('recurring_pricing_model');
                    if ($model === 'tiered' || $model === 'usage-based') {
                        $set('billing_scheme', 'tiered');
                    }
                }),

            Section::make('Usage Configuration')
                ->description('Link to a meter to price your customers usage.')
                ->schema([
                    Select::make('usage_meter_id')
                        ->label('Meter')
                        ->relationship('usageMeters', 'display_name')
                        ->createOptionForm([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->extraInputAttributes(['data-dusk' => 'usage-meter-name']),
                            TextInput::make('display_name')
                                ->required()
                                ->maxLength(255)
                                ->extraInputAttributes(['data-dusk' => 'usage-meter-display-name']),
                            TextInput::make('description')
                                ->maxLength(255)
                                ->extraInputAttributes(['data-dusk' => 'usage-meter-description']),
                            TextInput::make('unit_label')
                                ->required()
                                ->maxLength(255)
                                ->extraInputAttributes(['data-dusk' => 'usage-meter-unit-label']),
                        ])
                        ->required()
                        ->searchable()
                        ->preload()
                        ->extraAttributes(['data-dusk' => 'usage-meter-id'])
                        ->createOptionAction(
                            fn (Action $action) => $action->modalWidth('lg')
                        )
                        ->hidden(fn (Get $get): bool => $get('recurring_pricing_model') !== 'usage-based'),
                ])
                ->visible(
                    fn (Get $get): bool => $get('type') === 'recurring' &&
                        $get('recurring_pricing_model') === 'usage-based'
                ),

            Section::make('Advanced')
                ->schema([

                    KeyValue::make('metadata')
                        ->label('Metadata')
                        ->helperText('A list of product features that will be visible to customers. Displayed in pricing tables.')
                        ->columnSpanFull(),

                    TextInput::make('lookup_key')
                        ->label('Lookup Key')
                        ->helperText('Lookup keys make it easier to manage and make future pricing changes by using a unique key (e.g. standard_monthly) for each price, enabling easy querying and retrieval of specific prices. Lookup keys should be unique across all prices in your account.')
                        ->maxLength(255)
                        ->columnSpanFull(),

                ])
                ->collapsible(),
        ];
    }
}
