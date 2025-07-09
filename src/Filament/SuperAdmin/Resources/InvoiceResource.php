<?php

namespace App\Filament\SuperAdmin\Resources;

use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\SuperAdmin\Resources\InvoiceResource\Pages;
use App\Models\StripeInvoice;
use Illuminate\Support\HtmlString;

class InvoiceResource extends Resource
{
    protected static ?string $model = StripeInvoice::class;

    protected static ?string $modelLabel = 'Invoices';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([

                        Toggle::make('enable_invoice_generation')
                            ->required()
                            ->label('Enable invoice generation')
                            ->default(true)
                            ->helperText('If enabled, invoices will be generated for each successful transaction. Customers will be able to see their invoices in their dashboard.'),

                        TextInput::make('invoice_prefix')
                            ->label('Invoice Prefix')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('company_name')
                            ->label('Company Name')
                            ->maxLength(255),

                        TextInput::make('company_code')
                            ->label('Company Code')
                            ->maxLength(255),

                        TextInput::make('company_address')
                            ->label('Company Address')
                            ->maxLength(255),

                        TextInput::make('compaany_tax_number')
                            ->label('Company Tax Number (VAT)')
                            ->maxLength(255),

                        TextInput::make('company_phone')
                            ->label('Company Phone Number')
                            ->tel()
                            ->prefixIcon('heroicon-o-phone')
                            ->maxLength(255),

                        Placeholder::make('preview_invoice')
                            ->label('')
                            ->content(fn () => new HtmlString('
                        <div class="flex items-center justify-between">
                            <button class="rounded-lg py-1.5 px-4 border">
                                <div class="flex items-center gap-x-2">
                                    <div class="inline-flex items-center justify-center w-6 h-6 mr-2 text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </div>
                                    <div class="font-bold">Generate Preview</div>
                                </div>
                            </button>
                        </div>
                    ')),

                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
