<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeCoupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_id',
        'object',
        'amount_off',
        'created',
        'currency',
        'duration',
        'duration_in_months',
        'livemode',
        'max_redemptions',
        'metadata',
        'name',
        'percent_off',
        'redeem_by',
        'times_redeemed',
        'valid',
    ];

    protected $casts = [
        'amount_off' => 'integer',
        'created' => 'integer',
        'duration_in_months' => 'integer',
        'livemode' => 'boolean',
        'max_redemptions' => 'integer',
        'metadata' => 'array',
        'percent_off' => 'integer',
        'redeem_by' => 'integer',
        'times_redeemed' => 'integer',
        'valid' => 'boolean',
    ];

    /**
     * Get the discounts that use this coupon.
     */
    public function stripeDiscounts()
    {
        return $this->hasMany(StripeDiscount::class, 'coupon', 'stripe_id');
    }

    /**
     * Get the promotion codes that use this coupon.
     */
    public function stripePromotionCodes()
    {
        return $this->hasMany(StripePromotionCode::class, 'coupon', 'stripe_id');
    }

    /**
     * Check if the coupon is currently valid.
     */
    public function isValid(): bool
    {
        // Check if coupon is marked as valid
        if (! $this->valid) {
            return false;
        }

        // Check if coupon has expired
        if ($this->redeem_by && now()->timestamp > $this->redeem_by) {
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
        if ($this->percent_off) {
            return $this->percent_off.'%';
        }

        if ($this->amount_off) {
            $currency = $this->currency ?? 'usd';
            $amount = number_format($this->amount_off / 100, 2);

            return '$'.$amount.' '.strtoupper($currency);
        }

        return 'N/A';
    }

    /**
     * Get the discount duration as a formatted string.
     */
    public function getDiscountDurationAttribute(): string
    {
        $duration = $this->duration ?? 'once';
        $durationInMonths = $this->duration_in_months;

        return match ($duration) {
            'once' => 'One-time',
            'repeating' => $durationInMonths ? "Repeating ({$durationInMonths} months)" : 'Repeating',
            'forever' => 'Forever',
            default => ucfirst($duration)
        };
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
     * Get the redeem by date as a formatted string.
     */
    public function getRedeemByDateAttribute(): string
    {
        if (! $this->redeem_by) {
            return 'No expiration';
        }

        return date('M j, Y g:i A', $this->redeem_by);
    }

    /**
     * Check if the coupon has expired.
     */
    public function isExpired(): bool
    {
        if (! $this->redeem_by) {
            return false;
        }

        return now()->timestamp > $this->redeem_by;
    }

    /**
     * Check if the coupon has reached its maximum redemptions.
     */
    public function isMaxedOut(): bool
    {
        if (! $this->max_redemptions) {
            return false;
        }

        return $this->times_redeemed >= $this->max_redemptions;
    }

    /**
     * Get the redemption status as a string.
     */
    public function getRedemptionStatusAttribute(): string
    {
        if ($this->isMaxedOut()) {
            return 'Maxed out';
        }

        if ($this->max_redemptions) {
            return "{$this->times_redeemed}/{$this->max_redemptions}";
        }

        return "{$this->times_redeemed} used";
    }

    /**
     * Get the coupon name or ID as display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->stripe_id;
    }
}
