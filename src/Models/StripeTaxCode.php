<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeTaxCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_id',
        'object',
        'description',
        'name',
    ];

    protected $casts = [
        // No special casts needed for this model
    ];

    /**
     * Get the products that use this tax code.
     */
    public function stripeProducts()
    {
        return $this->hasMany(StripeProduct::class, 'tax_code', 'stripe_id');
    }

    /**
     * Get the display name for the tax code.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->stripe_id;
    }

    /**
     * Get a shortened description for display.
     */
    public function getShortDescriptionAttribute(): string
    {
        if (! $this->description) {
            return 'No description available';
        }

        // Limit to 100 characters and add ellipsis if longer
        if (strlen($this->description) > 100) {
            return substr($this->description, 0, 100).'...';
        }

        return $this->description;
    }

    /**
     * Check if this is a common tax code.
     */
    public function isCommonTaxCode(): bool
    {
        $commonCodes = [
            'txcd_99999999', // General - Tangible Goods
            'txcd_99999998', // General - Services
            'txcd_99999997', // General - Digital Goods
            'txcd_99999996', // General - Books
            'txcd_99999995', // General - Food
        ];

        return in_array($this->stripe_id, $commonCodes);
    }

    /**
     * Get the tax code category based on the name.
     */
    public function getCategoryAttribute(): string
    {
        if (! $this->name) {
            return 'Unknown';
        }

        $name = strtolower($this->name);

        if (str_contains($name, 'tangible') || str_contains($name, 'goods')) {
            return 'Tangible Goods';
        }

        if (str_contains($name, 'service')) {
            return 'Services';
        }

        if (str_contains($name, 'digital')) {
            return 'Digital Goods';
        }

        if (str_contains($name, 'book')) {
            return 'Books';
        }

        if (str_contains($name, 'food')) {
            return 'Food';
        }

        return 'General';
    }

    /**
     * Scope to get common tax codes.
     */
    public function scopeCommon($query)
    {
        return $query->whereIn('stripe_id', [
            'txcd_99999999', // General - Tangible Goods
            'txcd_99999998', // General - Services
            'txcd_99999997', // General - Digital Goods
            'txcd_99999996', // General - Books
            'txcd_99999995', // General - Food
        ]);
    }

    /**
     * Scope to get tax codes by category.
     */
    public function scopeByCategory($query, $category)
    {
        $category = strtolower($category);

        return $query->where(function ($q) use ($category) {
            $q->where('name', 'like', "%{$category}%")
                ->orWhere('description', 'like', "%{$category}%");
        });
    }

    /**
     * Get the number of products using this tax code.
     */
    public function getProductCountAttribute(): int
    {
        return $this->stripeProducts()->count();
    }

    /**
     * Get the total usage count.
     */
    public function getUsageCountAttribute(): int
    {
        return $this->product_count;
    }
}
