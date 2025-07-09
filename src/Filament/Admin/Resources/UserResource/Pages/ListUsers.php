<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use App\Filament\Admin\Resources\UserResource;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.admin.resources.user-resource.pages.list-users';

    public $search = '';

    // Override table to ensure search works properly
    public function table(Tables\Table $table): Tables\Table
    {
        $table = parent::table($table);

        return $table
            ->headerActions([]) // Disable default header actions since we render them in custom layout
            ->modifyQueryUsing(function (Builder $query): Builder {
                if ($this->search) {
                    $query->where(function ($q) {
                        $q->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%');
                    });
                }

                return $query;
            });
    }

    // Handle live updates when search changes
    public function updatedSearch()
    {
        $this->resetTable();
    }

    // Only used in the custom search row
    protected function getCustomHeaderActions(): array
    {
        return [
            Tables\Actions\ActionGroup::make([
                Tables\Actions\ImportAction::make()
                    ->label('Import Users')
                    ->icon('heroicon-o-arrow-up-tray'),
                Tables\Actions\ExportAction::make()
                    ->label('Export Users')
                    ->icon('heroicon-o-arrow-down-tray'),
            ])
                ->label('Actions')
                ->icon('heroicon-o-ellipsis-horizontal')
                ->color('gray'),
            Actions\CreateAction::make()->label('Create user'),
        ];
    }

    // Restore default header actions but add a hidden class
    protected function getHeaderActions(): array
    {
        return [
            Tables\Actions\ActionGroup::make([
                Tables\Actions\ImportAction::make()
                    ->label('Import Users')
                    ->icon('heroicon-o-arrow-up-tray'),
                Tables\Actions\ExportAction::make()
                    ->label('Export Users')
                    ->icon('heroicon-o-arrow-down-tray'),
            ])
                ->label('Actions')
                ->icon('heroicon-o-ellipsis-horizontal')
                ->color('gray')
                ->extraAttributes(['class' => 'hidden']),
            Actions\CreateAction::make()->label('Create user')->extraAttributes(['class' => 'hidden']),
        ];
    }

    // Override to disable default header actions in the table
    protected function getTableHeaderActions(): array
    {
        return [];
    }

    // Override to completely disable header actions
    protected function hasTableHeaderActions(): bool
    {
        return false;
    }
}
