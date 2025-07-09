<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class SelectCard extends Field
{
    protected string $view = 'forms.components.select-card';

    protected array $options = [];

    protected array $selectGroupExtraAttributes = [];

    protected ?string $duskSelector = null;

    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function duskSelector(?string $selector): static
    {
        $this->duskSelector = $selector;

        return $this;
    }

    public function getDuskSelector(): ?string
    {
        return $this->duskSelector;
    }

    public function selectGroupExtraAttributes(array $attributes): static
    {
        $this->selectGroupExtraAttributes = $attributes;

        return $this;
    }

    public function getSelectGroupExtraAttributes(): array
    {
        $attributes = $this->selectGroupExtraAttributes;

        if ($this->duskSelector) {
            $attributes['data-dusk'] = $this->duskSelector;
        }

        return $attributes;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrateStateUsing(function ($state) {
            return strval($state);
        });

        $this->live();
    }
}
