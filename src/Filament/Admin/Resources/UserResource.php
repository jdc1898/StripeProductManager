<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Users';

    protected static ?string $navigationGroup = 'Users';

    protected static ?int $navigationSort = 1;

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
            ->modifyQueryUsing(function (Builder $query) {
                $currentUser = Auth::user();
                if ($currentUser && $currentUser->tenant_id) {
                    $query->where('tenant_id', $currentUser->tenant_id);
                }

                return $query;
            })
            ->columns([
                ImageColumn::make('avatar')
                    ->label('')
                    ->circular()
                    ->size(40)
                    ->getStateUsing(function ($record) {
                        return $record->avatarUrl();
                    }),
                TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->weight('bold')
                    ->description(function ($record) {
                        return view('components.small-text', ['text' => $record->email]);
                    }),

                TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('membership_length')
                    ->label('Member since')
                    ->getStateUsing(function ($record) {
                        return $record->membershipLength();
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('created_at', $direction);
                    }),

                TextColumn::make('roles')
                    ->label('Roles')
                    ->badge()
                    ->color('primary')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->getStateUsing(function ($record) {
                        $roles = $record->getRoleNames();
                        $primaryRole = $roles->first();
                        $remainingCount = $roles->count() - 1;

                        if ($remainingCount > 0) {
                            return [$primaryRole, "+{$remainingCount} more"];
                        }

                        return [$primaryRole];
                    })
                    ->tooltip(function ($record) {
                        $roles = $record->getRoleNames();
                        if ($roles->count() > 1) {
                            return 'All roles: '.$roles->implode(', ');
                        }

                        return null;
                    })
                    ->sortable(false),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->hiddenLabel()->iconSize('lg')->color('gray')->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()->hiddenLabel()->iconSize('lg')->color('gray')->icon('heroicon-o-pencil-square'),
                Tables\Actions\DeleteAction::make()->hiddenLabel()->iconSize('lg')->color('gray')->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
            ])
            ->paginated([
                10,
                25,
                50,
                100,
            ])
            ->defaultPaginationPageOption(25);
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
            'index' => Pages\ListUsers::route('/'),
            // 'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
