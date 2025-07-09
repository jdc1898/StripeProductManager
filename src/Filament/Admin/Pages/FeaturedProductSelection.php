<?php

namespace App\Filament\Admin\Pages;

use App\Models\StripePrice;
use App\Models\StripeProduct;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class FeaturedProductSelection extends Page
{
    public string $plan = 'month';

    protected static ?string $navigationIcon = 'heroicon-s-fire';

    protected static ?string $title = 'Subscriptions';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Subscriptions';

    protected static string $view = 'filament.admin.pages.featured-product-selection';

    public array $availablePlans = [];

    public string $clientSecret;

    public ?int $currentPlanId = null;

    public function mount(): void
    {
        $this->clientSecret = Auth::user()->createSetupIntent()->client_secret;

        // Get current subscription safely
        $currentSubscription = Auth::user()->subscriptions()
            ->whereIn('stripe_status', ['active', 'trialing'])
            ->first();

        // Get the current product ID from the subscription's price
        $this->currentPlanId = null;
        if ($currentSubscription) {
            $currentPrice = StripePrice::where('stripe_id', $currentSubscription->stripe_price)->first();
            if ($currentPrice) {
                $currentProduct = StripeProduct::where('default_price', $currentPrice->stripe_id)->first();
                $this->currentPlanId = $currentProduct ? $currentProduct->id : null;
            }
        }

        $this->availablePlans = StripeProduct::where('active', true)
            ->get()
            ->map(function ($product) {
                $price = StripePrice::where('stripe_id', $product->default_price)->first();

                if (! $price) {
                    return null;
                }

                // Handle different pricing schemes
                $priceDisplay = 'Pay per use';
                $description = $product->description ?? 'Email service plan';

                if ($price->billing_scheme === 'tiered' && $price->hasTiers()) {
                    // Use tiered pricing summary for main display
                    $priceDisplay = $price->getTieredPricingSummary();
                    $description .= ' - '.$price->getTieredPricingDescription();
                } elseif ($price->unit_amount && $price->unit_amount > 0) {
                    // Standard pricing
                    $priceDisplay = '$'.ceil($price->unit_amount / 100);
                } elseif ($price->recurring && ($price->recurring['usage_type'] ?? 'licensed') === 'metered') {
                    // Metered pricing
                    $priceDisplay = 'Pay per use';
                    $description .= ' - Metered billing';
                }

                return [
                    'id' => $product->id,
                    'price_id' => $price->stripe_id,
                    'name' => $product->name,
                    'description' => $description,
                    'price' => $priceDisplay,
                    'interval' => $price->recurring['interval'] ?? 'month',
                    'interval_count' => $price->recurring['interval_count'] ?? 1,
                    'billing_scheme' => $price->billing_scheme,
                    'has_tiers' => $price->hasTiers(),
                    'tiers' => $price->tiers,
                    'usage_type' => $price->recurring['usage_type'] ?? 'licensed',
                    'features' => $product->metadata['features'] ?? [],
                ];
            })
            ->filter() // Remove null values
            ->toArray();
    }

    protected function getViewData(): array
    {
        return [
            'availablePlans' => $this->availablePlans,
            'plan' => $this->plan,
            'currentPlanId' => $this->currentPlanId,
            'clientSecret' => $this->clientSecret,
        ];
    }
}
