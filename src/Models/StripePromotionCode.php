<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripePromotionCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_id',
        'object',
        'active',
        'code',
        'coupon',
        'created',
        'customer',
        'expires_at',
        'livemode',
        'max_redemptions',
        'metadata',
        'restrictions',
        'times_redeemed',
    ];

    protected $casts = [
        'active' => 'boolean',
        'coupon' => 'array',
        'created' => 'integer',
        'expires_at' => 'integer',
        'livemode' => 'boolean',
        'max_redemptions' => 'integer',
        'metadata' => 'array',
        'restrictions' => 'array',
        'times_redeemed' => 'integer',
    ];

    /**
     * Get the customer that this promotion code belongs to.
     */
    public function stripeCustomer()
    {
        return $this->belongsTo(StripeCustomer::class, 'customer', 'stripe_id');
    }

    /**
     * Get the discounts that use this promotion code.
     */
    public function stripeDiscounts()
    {
        return $this->hasMany(StripeDiscount::class, 'promotion_code', 'stripe_id');
    }

    /**
     * Get the coupon that this promotion code belongs to.
     */
    public function stripeCoupon()
    {
        return $this->belongsTo(StripeCoupon::class, 'coupon', 'stripe_id');
    }

    /**
     * Check if the promotion code is currently valid.
     */
    public function isValid(): bool
    {
        // Check if promotion code is active
        if (! $this->active) {
            return false;
        }

        // Check if promotion code has expired
        if ($this->expires_at && now()->timestamp > $this->expires_at) {
            return false;
        }

        // Check if max redemptions reached
        if ($this->max_redemptions && $this->times_redeemed >= $this->max_redemptions) {
            return false;
        }

        return true;
    }

    /**
     * Get the discount amount as a formatted string.
     */
    public function getDiscountAmountAttribute(): string
    {
        if (! $this->coupon) {
            return 'N/A';
        }

        if (isset($this->coupon['percent_off'])) {
            return $this->coupon['percent_off'].'%';
        }

        if (isset($this->coupon['amount_off'])) {
            $currency = $this->coupon['currency'] ?? 'usd';
            $amount = number_format($this->coupon['amount_off'] / 100, 2);

            return '$'.$amount.' '.strtoupper($currency);
        }

        return 'N/A';
    }

    /**
     * Get the discount duration as a formatted string.
     */
    public function getDiscountDurationAttribute(): string
    {
        if (! $this->coupon) {
            return 'N/A';
        }

        $duration = $this->coupon['duration'] ?? 'once';
        $durationInMonths = $this->coupon['duration_in_months'] ?? null;

        return match ($duration) {
            'once' => 'One-time',
            'repeating' => $durationInMonths ? "Repeating ({$durationInMonths} months)" : 'Repeating',
            'forever' => 'Forever',
            default => ucfirst($duration)
        };
    }

    /**
     * Get the expiration date as a formatted string.
     */
    public function getExpirationDateAttribute(): string
    {
        if (! $this->expires_at) {
            return 'Never expires';
        }

        return date('M j, Y g:i A', $this->expires_at);
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
     * Check if the promotion code has usage restrictions.
     */
    public function hasRestrictions(): bool
    {
        if (! $this->restrictions) {
            return false;
        }

        return ! empty(array_filter($this->restrictions, fn ($value) => $value !== null && $value !== false));
    }

    /**
     * Get the minimum amount restriction as a formatted string.
     */
    public function getMinimumAmountAttribute(): string
    {
        if (! $this->restrictions || ! isset($this->restrictions['minimum_amount'])) {
            return 'No minimum';
        }

        $amount = $this->restrictions['minimum_amount'];
        $currency = $this->restrictions['minimum_amount_currency'] ?? 'usd';

        return '$'.number_format($amount / 100, 2).' '.strtoupper($currency);
    }
}
