<?php

namespace App\Filament\SuperAdmin\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class MainDashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static string $routePath = 'main';

    protected static ?string $title = 'Main dashboard';

    protected static ?int $navigationSort = 1;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        DatePicker::make('startDate'),
                        DatePicker::make('endDate'),
                        Select::make('period')
                            ->options([
                                'day' => 'Day',
                                'week' => 'Week',
                                'month' => 'Month',
                                'year' => 'year',
                            ])
                            ->default('month')
                            ->label('Period'),
                    ])
                    ->columns(3),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\SuperAdmin\Widgets\MrrChartStatsWidget::class,
            \App\Filament\SuperAdmin\Widgets\MrrStatsWidget::class,
            \App\Filament\SuperAdmin\Widgets\ActiveSubscriptionsWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'xl' => 3,
        ];
    }
}
