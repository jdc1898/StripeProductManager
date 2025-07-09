<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeTaxRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_id',
        'object',
        'active',
        'country',
        'created',
        'description',
        'display_name',
        'inclusive',
        'jurisdiction',
        'livemode',
        'metadata',
        'percentage',
        'state',
        'tax_type',
    ];

    protected $casts = [
        'active' => 'boolean',
        'created' => 'integer',
        'inclusive' => 'boolean',
        'livemode' => 'boolean',
        'metadata' => 'array',
        'percentage' => 'decimal:2',
    ];

    /**
     * Get the display name for the tax rate.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->display_name ?: $this->description ?: $this->stripe_id;
    }

    /**
     * Get the formatted percentage for display.
     */
    public function getFormattedPercentageAttribute(): string
    {
        if (! $this->percentage) {
            return 'N/A';
        }

        return $this->percentage.'%';
    }

    /**
     * Get the creation date as a formatted string.
     */
    public function getCreationDateAttribute(): string
    {
        if (! $this->created) {
            return 'N/A';
        }

        return date('M j, Y g:i A', $this->created);
    }

    /**
     * Get the jurisdiction display name.
     */
    public function getJurisdictionDisplayAttribute(): string
    {
        if (! $this->jurisdiction) {
            return 'Global';
        }

        // Map common jurisdiction codes to readable names
        $jurisdictions = [
            'DE' => 'Germany',
            'US' => 'United States',
            'CA' => 'Canada',
            'GB' => 'United Kingdom',
            'FR' => 'France',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'NL' => 'Netherlands',
            'BE' => 'Belgium',
            'AT' => 'Austria',
            'IE' => 'Ireland',
            'LU' => 'Luxembourg',
            'PT' => 'Portugal',
            'GR' => 'Greece',
            'FI' => 'Finland',
            'SE' => 'Sweden',
            'DK' => 'Denmark',
            'NO' => 'Norway',
            'CH' => 'Switzerland',
            'AU' => 'Australia',
            'NZ' => 'New Zealand',
            'JP' => 'Japan',
            'KR' => 'South Korea',
            'SG' => 'Singapore',
            'HK' => 'Hong Kong',
            'IN' => 'India',
            'BR' => 'Brazil',
            'MX' => 'Mexico',
            'AR' => 'Argentina',
            'CL' => 'Chile',
            'CO' => 'Colombia',
            'PE' => 'Peru',
            'UY' => 'Uruguay',
            'PY' => 'Paraguay',
            'BO' => 'Bolivia',
            'EC' => 'Ecuador',
            'VE' => 'Venezuela',
            'GY' => 'Guyana',
            'SR' => 'Suriname',
            'GF' => 'French Guiana',
            'FK' => 'Falkland Islands',
            'GS' => 'South Georgia',
            'BV' => 'Bouvet Island',
            'AQ' => 'Antarctica',
        ];

        return $jurisdictions[$this->jurisdiction] ?? $this->jurisdiction;
    }

    /**
     * Get the location display (country + state).
     */
    public function getLocationDisplayAttribute(): string
    {
        $parts = [];

        if ($this->country) {
            $parts[] = $this->country;
        }

        if ($this->state) {
            $parts[] = $this->state;
        }

        if (empty($parts)) {
            return 'Global';
        }

        return implode(', ', $parts);
    }

    /**
     * Check if this is a VAT tax rate.
     */
    public function isVat(): bool
    {
        return str_contains(strtolower($this->description ?? ''), 'vat') ||
               str_contains(strtolower($this->display_name ?? ''), 'vat');
    }

    /**
     * Check if this is a GST tax rate.
     */
    public function isGst(): bool
    {
        return str_contains(strtolower($this->description ?? ''), 'gst') ||
               str_contains(strtolower($this->display_name ?? ''), 'gst');
    }

    /**
     * Check if this is a sales tax rate.
     */
    public function isSalesTax(): bool
    {
        return str_contains(strtolower($this->description ?? ''), 'sales tax') ||
               str_contains(strtolower($this->display_name ?? ''), 'sales tax');
    }

    /**
     * Get the tax type category.
     */
    public function getTaxTypeCategoryAttribute(): string
    {
        if ($this->isVat()) {
            return 'VAT';
        }

        if ($this->isGst()) {
            return 'GST';
        }

        if ($this->isSalesTax()) {
            return 'Sales Tax';
        }

        return $this->tax_type ?: 'Other';
    }

    /**
     * Scope to get active tax rates.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get tax rates by country.
     */
    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Scope to get tax rates by jurisdiction.
     */
    public function scopeByJurisdiction($query, $jurisdiction)
    {
        return $query->where('jurisdiction', $jurisdiction);
    }

    /**
     * Scope to get inclusive tax rates.
     */
    public function scopeInclusive($query)
    {
        return $query->where('inclusive', true);
    }

    /**
     * Scope to get exclusive tax rates.
     */
    public function scopeExclusive($query)
    {
        return $query->where('inclusive', false);
    }

    /**
     * Scope to get VAT tax rates.
     */
    public function scopeVat($query)
    {
        return $query->where(function ($q) {
            $q->where('description', 'like', '%VAT%')
                ->orWhere('display_name', 'like', '%VAT%');
        });
    }

    /**
     * Scope to get GST tax rates.
     */
    public function scopeGst($query)
    {
        return $query->where(function ($q) {
            $q->where('description', 'like', '%GST%')
                ->orWhere('display_name', 'like', '%GST%');
        });
    }

    /**
     * Scope to get sales tax rates.
     */
    public function scopeSalesTax($query)
    {
        return $query->where(function ($q) {
            $q->where('description', 'like', '%sales tax%')
                ->orWhere('display_name', 'like', '%sales tax%');
        });
    }

    /**
     * Calculate tax amount for a given amount.
     */
    public function calculateTax(float $amount): float
    {
        if (! $this->percentage) {
            return 0;
        }

        return $amount * ($this->percentage / 100);
    }

    /**
     * Calculate amount with tax included.
     */
    public function calculateAmountWithTax(float $amount): float
    {
        if (! $this->percentage) {
            return $amount;
        }

        if ($this->inclusive) {
            return $amount;
        }

        return $amount + $this->calculateTax($amount);
    }

    /**
     * Calculate amount without tax.
     */
    public function calculateAmountWithoutTax(float $amount): float
    {
        if (! $this->percentage) {
            return $amount;
        }

        if (! $this->inclusive) {
            return $amount;
        }

        return $amount / (1 + ($this->percentage / 100));
    }
}
