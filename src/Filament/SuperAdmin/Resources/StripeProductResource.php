<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\Forms\Builders\ProductFormBuilder;
use App\Filament\SuperAdmin\Resources\StripeProductResource\Pages;
use App\Filament\SuperAdmin\Resources\StripeProductResource\RelationManagers\PriceRelationManager;
use App\Filament\SuperAdmin\Resources\StripeProductResource\Tables\StripeProductResourceTable;
use App\Filament\SuperAdmin\Widgets\ProductStatsWidget;
// use Filament\Infolists\Components\Actions;
// use Filament\Infolists\Components\Actions\Action;
use App\Models\StripeProduct;
use Filament\Forms\Form;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StripeProductResource extends Resource
{
    private const GROUP_STRIPE = 'stripe';

    private const GROUP_ACTIONS = 'actions';

    protected static ?string $model = StripeProduct::class;

    // protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Products';

    protected static ?string $navigationGroup = 'Product Management';

    protected static ?int $navigationSort = 1;

    public static function getHeaderWidgets(): array
    {
        return [
            // ProductStatsWidget::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('active', true)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema(ProductFormBuilder::getAdvancedPricingForm());
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function table(Table $table): Table
    {
        return StripeProductResourceTable::make($table);
    }

    public static function getRelations(): array
    {
        return [
            PriceRelationManager::class,
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Product Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Name'),

                        TextEntry::make('stripe_id')
                            ->label('Stripe ID'),

                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'prose prose-invert'])
                            ->html(),

                        TextEntry::make('active')
                            ->label('Status')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive'),
                    ]),

                InfolistSection::make('Product Metadata')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        KeyValueEntry::make('metadata'),
                        TextEntry::make('images')
                            ->label('Images')
                            ->listWithLineBreaks()
                            ->formatStateUsing(function ($state) {
                                if (! $state || ! is_array($state)) {
                                    return 'No images';
                                }

                                return collect($state)->map(fn ($url) => $url)->join("\n");
                            }),
                        TextEntry::make('marketing_features')
                            ->label('Marketing Features')
                            ->listWithLineBreaks()
                            ->formatStateUsing(function ($state) {
                                if (! $state || ! is_array($state)) {
                                    return 'No marketing features';
                                }

                                return collect($state)->map(fn ($feature) => $feature['name'] ?? 'Unknown feature')->join("\n");
                            }),
                        TextEntry::make('package_dimensions')
                            ->label('Package Dimensions')
                            ->formatStateUsing(function ($state) {
                                if (! $state || ! is_array($state)) {
                                    return 'No package dimensions';
                                }

                                return json_encode($state, JSON_PRETTY_PRINT);
                            }),
                    ]),

                InfolistSection::make('Stripe API Information')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('stripe_id')
                            ->label('Stripe Product ID')
                            ->copyable(),
                        TextEntry::make('created')
                            ->label('Created in Stripe')
                            ->formatStateUsing(fn ($state) => $state ? date('M j, Y g:i A', $state) : 'N/A'),
                        TextEntry::make('updated')
                            ->label('Updated in Stripe')
                            ->formatStateUsing(fn ($state) => $state ? date('M j, Y g:i A', $state) : 'N/A'),
                        TextEntry::make('livemode')
                            ->label('Live Mode')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'danger' : 'success')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Live' : 'Test'),
                        TextEntry::make('tax_code')
                            ->label('Tax Code'),
                        TextEntry::make('statement_descriptor')
                            ->label('Statement Descriptor'),
                        TextEntry::make('unit_label')
                            ->label('Unit Label'),
                        TextEntry::make('url')
                            ->label('Product URL')
                            ->url(fn ($state) => $state),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            // 'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
        ];
    }
}
