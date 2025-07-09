<?php

namespace App\Filament\Forms\Builders;

use App\Filament\Forms\Components\RadioCard;
use App\Filament\Forms\Components\SelectCard;
use App\Filament\Forms\Components\TieredPricing;
use App\Models\StripeProduct;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class ProductFormBuilder
{
    public static function getAdvancedPricingForm(): array
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
                            $set('name', $testProduct['name']);
                            $set('active', false);
                            $set('description', $testProduct['description']);
                            $set('metadata', $testProduct['metadata']);
                            $set('tax_code', $testProduct['tax_code']);
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
                            $set('amount', rand(100, 9999));
                            $set('billing_period', 'month');
                            $set('tax_behavior', 'exclusive');
                            $set('price_description', 'Default price for '.$testProduct['name']);
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

                            $set('name', $testProduct['name']);
                            $set('active', false);
                            $set('description', $testProduct['description']);
                            $set('metadata', $testProduct['metadata']);
                            $set('tax_code', $testProduct['tax_code']);
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
                            $set('price_description', 'Default price for '.$testProduct['name']);
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
                            $set('name', $testProduct['name']);
                            $set('active', false);
                            $set('description', $testProduct['description']);
                            $set('metadata', $testProduct['metadata']);
                            $set('tax_code', $testProduct['tax_code']);
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
                            $set('price_description', 'Default price for '.$testProduct['name']);
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
                            $set('description', $testProduct['description']);
                            $set('billing_type', 'recurring');
                            // $set('recurring_pricing_model', 'flat-rate');
                            $set('amount', 1000);
                            $set('billing_period', 'month');
                            $set('tax_behavior', 'exclusive');

                            $set('price_description', 'Default Plan');
                            $set('lookup_key', 'default-plan');
                        }
                    })
                    ->button(),

            ])->columnSpanFull(),

            TextInput::make('name')
                ->label('Name')
                ->required()
                ->helperText('Name of the product or service, visible to customers.')
                ->maxLength(255)
                ->columnSpanFull()
                ->extraInputAttributes(['data-dusk' => 'name']),

            Textarea::make('description')
                ->label('Description')
                ->helperText('Appears at checkout, on the customer portal, and in quotes.')
                ->maxLength(255)
                ->rows(3)
                ->columnSpanFull()
                ->extraInputAttributes(['data-dusk' => 'description']),

            SelectCard::make('tax_code')
                ->label('Product Tax Code')
                ->required()
                ->helperText('This will be used for calculating automatic tax. Defaults to the preset product tax code from your tax settings.')
                ->columnSpanFull()
                ->options([
                    'txcd_10701300' => [
                        'title' => 'Website Data Processing',
                        'description' => 'An online service that allows a customer to create, transform, process or access data electronically.',
                    ],
                ])
                ->default('txcd_10701300')
                ->duskSelector('tax-code')
                ->live(),

            RadioCard::make('billing_type')
                ->options([
                    'recurring' => 'Recurring',
                    'one-time' => 'One-off',
                ])
                ->descriptions([
                    'recurring' => 'Charge an ongoing fee',
                    'one-time' => 'Charge a one-time fee',
                ])
                ->hiddenLabel()
                ->columnSpanFull()
                ->default('recurring')
                ->duskSelector('billing-type')
                ->live(),

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
                ->visible(fn (Get $get) => $get('billing_type') === 'one-time')
                ->default('flat-rate')
                ->duskSelector('one-off-pricing-model')
                ->live(),

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
                ->visible(fn (Get $get) => $get('billing_type') === 'recurring')
                ->default('flat-rate')
                ->duskSelector('recurring-pricing-model')
                ->live(),

            Checkbox::make('has_suggested_amount')
                ->label('Suggest a preset amount')
                ->columnSpanFull()
                ->visible(
                    fn (Get $get): bool => $get('billing_type') === 'one-time' &&
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
                    fn (Get $get): bool => $get('billing_type') === 'one-time' &&
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
                ->visible(fn (Get $get): bool => $get('billing_type') === 'recurring' &&
                    $get('recurring_pricing_model') === 'tiered'
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
                    fn (Get $get): bool => $get('billing_type') === 'recurring' &&
                        $get('recurring_pricing_model') === 'usage-based'
                ),

            TextInput::make('amount')
                ->label('Amount')
                ->prefix('$')
                ->suffix('USD')
                ->placeholder('0')
                ->required()
                ->columnSpanFull()
                ->helperText('The amount to charge for this product. This is used to generate the default price. This should be in cents.')
                ->extraInputAttributes(['data-dusk' => 'amount'])
                ->hidden(
                    fn (Get $get): bool => ($get('billing_type') === 'recurring' && $get('recurring_pricing_model') === 'tiered') ||
                        ($get('billing_type') === 'one-time' && $get('one_off_pricing_model') === 'customer-defined')
                ),

            TextInput::make('package_units')
                ->label('Package Size')
                ->prefix('per')
                ->hiddenLabel()
                ->suffix('units')
                ->columnSpanFull()
                ->numeric()
                ->minValue(1)
                ->placeholder('10')
                ->extraInputAttributes(['data-dusk' => 'package-units'])
                ->visible(function (Get $get) {
                    return $get('billing_type') === 'recurring' && $get('recurring_pricing_model') === 'package' ||
                    $get('billing_type') === 'recurring' && $get('recurring_pricing_model') === 'usage-based' && $get('usage_based_type') === 'package' ||
                    $get('billing_type') === 'one-time' && $get('one_off_pricing_model') === 'package';
                }),

            TieredPricing::make('tiers')
                ->columnSpanFull()
                ->extraAttributes(['data-dusk' => 'tiers'])
                ->visible(
                    fn (Get $get): bool => $get('billing_type') === 'recurring' && $get('recurring_pricing_model') === 'tiered' ||
                    $get('billing_type') === 'recurring' && $get('recurring_pricing_model') === 'usage-based' && $get('usage_based_type') === 'tier'
                ),

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
                    'custom' => [
                        'title' => 'Custom',
                        'description' => 'Define a custom billing period',
                    ],
                ])
                ->default('month')
                ->duskSelector('billing-period')
                ->live(),

            Grid::make(2)
                ->schema([
                    TextInput::make('interval_count')
                        ->label('Every')
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->required()
                        ->extraInputAttributes(['data-dusk' => 'interval-count']),

                    Select::make('interval_type')
                        ->options([
                            'days' => 'Days',
                            'weeks' => 'Weeks',
                            'months' => 'Months',
                            'years' => 'Years',
                        ])
                        ->default('months')
                        ->required()
                        ->extraAttributes(['data-dusk' => 'interval-type']),
                ])
                ->columns(2)
                ->hidden(fn (Get $get): bool => $get('billing_period') !== 'custom'),

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
                ->default(false)
                ->live()
                ->duskSelector('tax-behavior'),

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
                ->visible(fn (Get $get): bool => $get('billing_type') === 'recurring' &&
                    $get('recurring_pricing_model') === 'usage-based'
                ),

            Section::make('Advanced')
                ->schema([

                    TextInput::make('price_description')
                        ->label('Price description')
                        ->helperText('Use to organize your prices. Not shown to customers.')
                        ->maxLength(255)
                        ->extraInputAttributes(['data-dusk' => 'price-description']),

                    TextInput::make('lookup_key')
                        ->label('Lookup key')
                        ->helperText('Lookup keys make it easier to manage and make future pricing changes by using a unique key (e.g. standard_monthly) for each price, enabling easy querying and retrieval of specific prices. Lookup keys should be unique across all prices in your account.')
                        ->maxLength(255)
                        ->extraInputAttributes(['data-dusk' => 'lookup-key']),

                    KeyValue::make('metadata')
                        ->label('Metadata')
                        ->helperText('A list of product features that will be visible to customers. Displayed in pricing tables.')
                        ->extraAttributes(['data-dusk' => 'metadata']),

                    Repeater::make('marketing_features')
                        ->label('Marketing features')
                        ->helperText('A list of product features that will be visible to customers. Displayed in pricing tables.')
                        ->schema([
                            TextInput::make('name')
                                ->label('Name')
                                ->maxLength(80)
                                ->columnSpanFull(),
                        ])
                        ->maxItems(15)
                        ->columns(2),

                    Toggle::make('shippable')
                        ->label('Shippable')
                        ->helperText('Whether this product is shipped (i.e., physical goods).')
                        ->extraAttributes(['data-dusk' => 'shippable'])
                        ->default(false),

                    KeyValue::make('package_dimensions')
                        ->label('Package dimensions')
                        ->helperText('The dimensions of this product for shipping purposes.')
                        ->extraAttributes(['data-dusk' => 'package-dimensions']),

                    TextInput::make('statement_descriptor')
                        ->label('Statement descriptor')
                        ->maxLength(22)
                        ->helperText('Overrides default descriptors. Only used for subscription payments. Pick something your customers will recognize on their bank statement.')
                        ->extraInputAttributes(['data-dusk' => 'statement-descriptor']),

                    TextInput::make('unit_label')
                        ->label('Unit label')
                        ->maxLength(255)
                        ->helperText('Describes how you sell your product, e.g. seats, tiers. Appears on each line item. Appears on receipts, invoices, at checkout, and on the customer portal.')
                        ->extraInputAttributes(['data-dusk' => 'unit-label']),

                    TextInput::make('url')
                        ->label('URL')
                        ->helperText('A URL of a publicly-accessible webpage for this product.')
                        ->maxLength(255)
                        ->extraInputAttributes(['data-dusk' => 'url']),

                ])
                ->collapsible(),
        ];
    }

    public static function getOneOffPricingForm(): array
    {
        return [
            TextInput::make('amount')
                ->label('Amount')
                ->columnSpanFull()
                ->prefix('$')
                ->numeric()
                ->placeholder('0.00')
                ->required()
                ->visible(fn (Get $get) => $get('pricing_model') !== 'customer-defined'),

            Grid::make(2)
                ->schema([
                    Checkbox::make('suggest_amount')
                        ->label('Suggest a preset amount')
                        ->live(),

                    Checkbox::make('set_limits')
                        ->label('Set limits')
                        ->live(),
                ])
                ->visible(fn (Get $get) => $get('pricing_model') === 'customer-defined'),

            TextInput::make('suggested_amount')
                ->label('Suggested Amount')
                ->prefix('$')
                ->numeric()
                ->placeholder('0.00')
                ->visible(fn (Get $get) => $get('pricing_model') === 'customer-defined' &&
                    $get('suggest_amount')
                ),

            Grid::make(2)
                ->schema([
                    TextInput::make('minimum_amount')
                        ->label('Minimum Amount')
                        ->prefix('$')
                        ->numeric()
                        ->placeholder('0.00'),

                    TextInput::make('maximum_amount')
                        ->label('Maximum Amount')
                        ->prefix('$')
                        ->numeric()
                        ->placeholder('0.00'),
                ])
                ->visible(fn (Get $get) => $get('pricing_model') === 'customer-defined' &&
                    $get('set_limits')
                ),
        ];
    }
}
