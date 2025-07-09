<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class TieredPricing extends Field
{
    protected string $view = 'forms.components.tiered-pricing';

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([
            [
                'first_unit' => 1,
                'last_unit' => 1,
                // 'per_unit' => 0.00,
                // 'flat_fee' => 0.00,
            ],
        ]);
    }
}
