<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_id',
        'user_id',
        'address',
        'balance',
        'created',
        'currency',
        'default_source',
        'delinquent',
        'description',
        'email',
        'invoice_prefix',
        'invoice_settings',
        'livemode',
        'metadata',
        'name',
        'next_invoice_sequence',
        'phone',
        'preferred_locales',
        'shipping',
        'tax_exempt',
        'test_clock',
    ];

    protected $casts = [
        'address' => 'array',
        'balance' => 'integer',
        'created' => 'integer',
        'delinquent' => 'boolean',
        'invoice_settings' => 'array',
        'livemode' => 'boolean',
        'metadata' => 'array',
        'next_invoice_sequence' => 'integer',
        'preferred_locales' => 'array',
        'shipping' => 'array',
    ];

    /**
     * Get the user that owns this Stripe customer.
     */
    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
