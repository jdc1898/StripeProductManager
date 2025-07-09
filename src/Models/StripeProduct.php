<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_id',
        'active',
        'created',
        'default_price',
        'description',
        'images',
        'marketing_features',
        'livemode',
        'metadata',
        'name',
        'package_dimensions',
        'shippable',
        'statement_descriptor',
        'tax_code',
        'unit_label',
        'updated',
        'url',
    ];

    protected $casts = [
        'active' => 'boolean',
        'created' => 'integer',
        'images' => 'array',
        'marketing_features' => 'array',
        'livemode' => 'boolean',
        'metadata' => 'array',
        'package_dimensions' => 'array',
        'shippable' => 'boolean',
        'updated' => 'integer',
    ];

    /**
     * Get the prices for this product.
     */
    public function stripePrices()
    {
        return $this->hasMany(StripePrice::class, 'product', 'stripe_id');
    }

    /**
     * Get the default price for this product.
     */
    public function defaultStripePrice()
    {
        return $this->belongsTo(StripePrice::class, 'default_price', 'stripe_id');
    }

    /**
     * Get the tax code for this product.
     */
    public function stripeTaxCode()
    {
        return $this->belongsTo(StripeTaxCode::class, 'tax_code', 'stripe_id');
    }
}
