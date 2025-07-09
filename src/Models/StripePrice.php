<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Subscription;

class StripePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_id',
        'active',
        'billing_scheme',
        'created',
        'currency',
        'custom_unit_amount',
        'livemode',
        'lookup_key',
        'metadata',
        'nickname',
        'product',
        'recurring',
        'tax_behavior',
        'tiers_mode',
        'tiers',
        'transform_quantity',
        'type',
        'unit_amount',
        'unit_amount_decimal',
    ];

    protected $casts = [
        'active' => 'boolean',
        'created' => 'integer',
        'custom_unit_amount' => 'array',
        'livemode' => 'boolean',
        'metadata' => 'array',
        'recurring' => 'array',
        'tiers' => 'array',
        'transform_quantity' => 'array',
        'unit_amount' => 'integer',
    ];

    /**
     * Get the product that this price belongs to.
     */
    public function stripeProduct()
    {
        return $this->belongsTo(StripeProduct::class, 'product', 'stripe_id');
    }

    /**
     * Get the products that use this price as their default price.
     */
    public function defaultForProducts()
    {
        return $this->hasMany(StripeProduct::class, 'default_price', 'stripe_id');
    }

    /**
     * Get all subscriptions that use this price.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'stripe_price', 'stripe_id');
    }

    /**
     * Get active subscriptions that use this price.
     */
    public function activeSubscriptions()
    {
        return $this->hasMany(Subscription::class, 'stripe_price', 'stripe_id')
            ->whereNull('ends_at');
    }

    /**
     * Get the formatted amount with currency symbol
     */
    public function formatForDisplay(): string
    {
        return '$'.number_format($this->unit_amount / 100, 2);
    }

    /**
     * Check if this price has tiered pricing
     */
    public function hasTiers(): bool
    {
        return $this->billing_scheme === 'tiered' && ! empty($this->tiers);
    }

    /**
     * Get the tiered pricing description
     */
    public function getTieredPricingDescription(): string
    {
        if (! $this->hasTiers()) {
            return '';
        }

        $descriptions = [];
        foreach ($this->tiers as $tier) {
            if (isset($tier['flat_amount']) && $tier['flat_amount'] > 0) {
                $amount = '$'.number_format($tier['flat_amount'] / 100, 2);
                if (isset($tier['up_to'])) {
                    $descriptions[] = "{$amount} for up to ".number_format($tier['up_to']).' emails';
                } else {
                    $descriptions[] = $amount;
                }
            } elseif (isset($tier['unit_amount']) && $tier['unit_amount'] > 0) {
                $amount = '$'.number_format($tier['unit_amount'] / 100, 2);
                if (isset($tier['up_to'])) {
                    $descriptions[] = "{$amount} per email after ".number_format($tier['up_to']).' emails';
                } else {
                    $descriptions[] = "{$amount} per email";
                }
            }
        }

        return implode(', ', $descriptions);
    }

    public function getTieredPricingSummary(): string
    {
        if (! $this->hasTiers()) {
            return '';
        }

        // Get the first tier for a summary
        $firstTier = $this->tiers[0] ?? null;
        if (! $firstTier) {
            return '';
        }

        if (isset($firstTier['flat_amount']) && $firstTier['flat_amount'] > 0) {
            $amount = $firstTier['flat_amount'] / 100;

            return '$'.($amount == (int) $amount ? number_format($amount, 0) : number_format($amount, 2));
        } elseif (isset($firstTier['unit_amount']) && $firstTier['unit_amount'] > 0) {
            $amount = $firstTier['unit_amount'] / 100;

            return '$'.($amount == (int) $amount ? number_format($amount, 0) : number_format($amount, 2)).' per email';
        }

        return 'Tiered pricing';
    }

    /**
     * Get the first tier's flat amount (if any)
     */
    public function getFirstTierFlatAmount(): ?int
    {
        if (! $this->hasTiers() || empty($this->tiers[0]['flat_amount'])) {
            return null;
        }

        return $this->tiers[0]['flat_amount'];
    }

    /**
     * Get the first tier's unit amount (if any)
     */
    public function getFirstTierUnitAmount(): ?int
    {
        if (! $this->hasTiers() || empty($this->tiers[0]['unit_amount'])) {
            return null;
        }

        return $this->tiers[0]['unit_amount'];
    }
}
