<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Concerns\HasOptions;
use Filament\Forms\Components\Field;

class RadioCard extends Field
{
    use HasOptions;

    protected string $view = 'forms.components.radio-card';

    protected array $descriptions = [];

    protected array $radioGroupExtraAttributes = [];

    protected ?string $duskSelector = null;

    public function descriptions(array $descriptions): static
    {
        $this->descriptions = $descriptions;

        return $this;
    }

    public function getDescriptions(): array
    {
        return $this->descriptions;
    }

    public function radioGroupExtraAttributes(array $attributes): static
    {
        $this->radioGroupExtraAttributes = $attributes;

        return $this;
    }

    public function getRadioGroupExtraAttributes(): array
    {
        $attributes = $this->radioGroupExtraAttributes;

        if ($this->duskSelector) {
            $attributes['data-dusk'] = $this->duskSelector;
        }

        return $attributes;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrateStateUsing(fn (string $state): ?string => filled($state) ? $state : null);
    }
}
