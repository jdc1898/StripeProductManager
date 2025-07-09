<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_id',
        'object',
        'checkout_session',
        'coupon',
        'customer',
        'end',
        'invoice',
        'invoice_item',
        'promotion_code',
        'start',
        'subscription',
    ];

    protected $casts = [
        'coupon' => 'array',
        'end' => 'datetime',
        'start' => 'datetime',
    ];

    /**
     * Get the customer that this discount belongs to.
     */
    public function stripeCustomer()
    {
        return $this->belongsTo(StripeCustomer::class, 'customer', 'stripe_id');
    }

    /**
     * Get the promotion code that this discount belongs to.
     */
    public function stripePromotionCode()
    {
        return $this->belongsTo(StripePromotionCode::class, 'promotion_code', 'stripe_id');
    }

    /**
     * Get the coupon that this discount belongs to.
     */
    public function stripeCoupon()
    {
        return $this->belongsTo(StripeCoupon::class, 'coupon', 'stripe_id');
    }

    /**
     * Check if the discount is currently active.
     */
    public function isActive(): bool
    {
        $now = now();

        // Check if discount has started
        if ($this->start && $now->lt($this->start)) {
            return false;
        }

        // Check if discount has ended
        if ($this->end && $now->gt($this->end)) {
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

        return match ($duration) {
            'once' => 'One-time',
            'repeating' => 'Repeating',
            'forever' => 'Forever',
            default => ucfirst($duration)
        };
    }
}
