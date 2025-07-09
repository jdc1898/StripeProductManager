<?php

namespace App\Services\Stripe;

use App\Models\StripeMeter;
use App\Models\StripePrice;
use App\Models\StripeProduct;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\PaymentMethod;
use Stripe\StripeClient;
use Throwable;

class StripeService
{
    protected StripeClient $client;

    public function __construct(?string $apiKey = null)
    {
        $this->client = new StripeClient($apiKey ?? config('stripe-product-manager.stripe.secret_key'));
    }

    /**
     * Get the underlying StripeClient instance
     */
    public function getClient(): StripeClient
    {
        return $this->client;
    }

    /**
     * Product
     */
    public function createProduct(array $data): string
    {
        try {
            $product = $this->client->products->create([
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
            ]);

            return $product->id;
        } catch (Throwable $e) {
            Log::error('Stripe Product Create Error', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    public function updateProduct(string $productId, array $data): string
    {
        try {
            $product = $this->client->products->update($productId, [
                'name' => $data['name'] ?? null,
                'description' => $data['description'] ?? null,
            ]);

            return $product->id;
        } catch (Throwable $e) {
            Log::error('Stripe Product Update Error', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    public function archiveProduct(string $productId): void
    {
        try {
            $this->client->products->update($productId, ['active' => false]);
        } catch (Throwable $e) {
            Log::error('Stripe Product Archive Error', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    public function getProductById(string $productId): ?object
    {
        try {
            return $this->client->products->retrieve($productId);
        } catch (Throwable $e) {
            Log::error('Stripe Product Retrieve Error', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function productExists(string $productId): bool
    {
        return $this->getProductById($productId) !== null;
    }

    /**
     * Price
     */
    public function createPrice(array $data): string
    {
        try {
            $priceData = [
                'currency' => $data['currency'],
                'unit_amount' => $data['unit_amount'],
                'product' => $data['product'],
            ];

            if (! empty($data['interval'])) {
                $priceData['recurring'] = ['interval' => $data['interval']];
            }

            $price = $this->client->prices->create($priceData);

            return $price->id;
        } catch (Throwable $e) {
            Log::error('Stripe Price Create Error', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    public function updatePrice(string $priceId, array $data): string
    {
        try {
            $price = $this->client->prices->update($priceId, [
                'name' => $data['name'] ?? null,
                'description' => $data['description'] ?? null,
            ]);

            return $price->id;
        } catch (Throwable $e) {
            Log::error('Stripe Price Update Error', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    public function archivePrice(string $priceId): void
    {
        try {
            $this->client->prices->update($priceId, ['active' => false]);
        } catch (Throwable $e) {
            Log::error('Stripe Price Archive Error', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    public function getPriceById(string $priceId): ?object
    {
        try {
            return $this->client->prices->retrieve($priceId);
        } catch (Throwable $e) {
            Log::error('Stripe Price Retrieve Error', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function priceExists(string $priceId): bool
    {
        return $this->getPriceById($priceId) !== null;
    }

    public function setDefaultPrice(string $productId, string $priceId): void
    {
        try {
            $this->client->products->update($productId, ['default_price' => $priceId]);
        } catch (Throwable $e) {
            Log::error('Stripe Default Price Set Error', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    /**
     * Customer
     */
    public function createCustomer(array $data): string
    {
        try {
            $customer = $this->client->customers->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'description' => $data['description'] ?? '',
                'metadata' => $data['metadata'] ?? [],
            ]);

            return $customer->id;
        } catch (Throwable $e) {
            Log::error('Stripe Customer Create Error', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    public function removeCustomer(string $customerId): void
    {
        try {
            $this->client->customers->delete($customerId);
        } catch (Throwable $e) {
            Log::error('Stripe Customer Remove Error', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    /**
     * Subscription
     */
    public function createSubscription(array $data): string
    {
        try {
            $subscription = $this->client->subscriptions->create([
                'customer' => $data['customer_id'],
                'items' => [['price' => $data['price_id']]],
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            return $subscription->id;
        } catch (Throwable $e) {
            Log::error('Stripe Subscription Create Error', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    /**
     * Coupons
     */
    public function createCoupon(array $data): string
    {
        try {
            $couponData = [];

            $type = $data['type'] ?? null;
            if ($type === 'percentage') {
                $couponData['percent_off'] = floatval($data['amount_off']);
            } else {
                $couponData['amount_off'] = intval($data['amount_off']);
                $couponData['currency'] = $data['currency'] ?? 'usd';
            }

            $couponData['duration'] = $data['duration'] ? 'forever' : 'once';
            $couponData['name'] = $data['name'] ?? null;

            if (! empty($data['max_redemptions'])) {
                $couponData['max_redemptions'] = $data['max_redemptions'];
            }

            if (! empty($data['valid_until'])) {
                $couponData['redeem_by'] = is_numeric($data['valid_until'])
                    ? intval($data['valid_until'])
                    : strtotime($data['valid_until']);
            }

            $coupon = $this->client->coupons->create($couponData);

            return $coupon->id;
        } catch (Throwable $e) {
            Log::error('Stripe Coupon Create Error', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    public function updateCoupon(string $couponId, array $data): string
    {
        try {
            $couponData = [];

            if (isset($data['name'])) {
                $couponData['name'] = $data['name'];
            }

            $coupon = $this->client->coupons->update($couponId, $couponData);

            return $coupon->id;

        } catch (Throwable $e) {
            Log::error('Stripe Coupon Update Error', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    public function getCouponById(string $couponId): ?object
    {
        try {
            return $this->client->coupons->retrieve($couponId);
        } catch (Throwable $e) {
            Log::error('Stripe Coupon Retrieve Error', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function archiveCoupon(string $couponId): void
    {
        try {
            $this->client->coupons->delete($couponId);
        } catch (Throwable $e) {
            Log::error('Stripe Coupon Archive Error', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    /**
     * Promotion Codes
     */
    public function createPromotionCode(array $data): string
    {
        try {
            $promoData = [
                'coupon' => $data['coupon'],
                'code' => $data['code'],
            ];

            if (isset($data['customer_id'])) {
                $promoData['customer'] = $data['customer_id'];
            }

            $promoCode = $this->client->promotionCodes->create($promoData);

            return $promoCode->id;
        } catch (Throwable $e) {
            Log::error('Stripe Promo Code Create Error', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    public function updatePromotionCode(string $promoCodeId, array $data): string
    {
        try {
            $promoData = [];

            if (isset($data['code'])) {
                $promoData['code'] = $data['code'];
            }

            if (isset($data['active'])) {
                $promoData['active'] = $data['active'];
            }

            if (isset($data['max_redemptions'])) {
                $promoData['max_redemptions'] = intval($data['max_redemptions']);
            }

            if (isset($data['expires_at'])) {
                $promoData['expires_at'] = is_numeric($data['expires_at'])
                    ? intval($data['expires_at'])
                    : strtotime($data['expires_at']);
            }

            $promoCode = $this->client->promotionCodes->update($promoCodeId, $promoData);

            return $promoCode->id;
        } catch (Throwable $e) {
            Log::error('Stripe Promo Code Update Error', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    public function archivePromotionCode(string $promoCodeId): void
    {
        try {
            $this->client->promotionCodes->update($promoCodeId, ['active' => false]);
        } catch (Throwable $e) {
            Log::error('Stripe Promo Code Archive Error', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    public function getPromotionCodeById(string $couponId): ?object
    {
        try {
            return $this->client->promotionCodes->retrieve($couponId);
        } catch (Throwable $e) {
            Log::error('Stripe Promo Code Retrieve Error', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Create Test Only Methods
     */
    public function createTestPaymentMethod(array $data): PaymentMethod
    {
        $cards = [
            'tok_visa',
            'tok_mastercard',
            'tok_amex',
        ];

        try {
            $paymentMethod = $this->client->paymentMethods->create([
                'type' => 'card',
                'card' => [
                    'token' => $data['token'] ?? $cards[array_rand($cards)],
                ],
                'billing_details' => [
                    'name' => $data['name'] ?? 'Test User',
                    'email' => $data['email'] ?? 'sample@example.com',
                ],
            ]);

            return $paymentMethod;
        } catch (Throwable $e) {
            Log::error('Stripe Test Payment Method Create Error', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    public function createTestClock()
    {
        try {
            $clock = $this->client->testHelpers->testClocks->create([
                'frozen_time' => time(),
                'name' => 'Redbird Test Clock - '.now()->toDateTimeString(),
            ]);

            return $clock->id;
        } catch (Throwable $e) {
            Log::error('Stripe Test Clock Create Error', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    public function listAllTimeClocks()
    {
        try {
            $clocks = $this->client->testHelpers->testClocks->all();

            return $clocks;
        } catch (Throwable $e) {
            Log::error('Stripe List Test Clocks Error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function getTestClockById(string $clockId)
    {
        try {
            return $this->client->testHelpers->testClocks->retrieve($clockId);
        } catch (Throwable $e) {
            Log::error('Stripe Get Test Clock Error', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function archiveTestClock(string $clockId): void
    {
        try {
            $this->client->testHelpers->testClocks->delete($clockId);
        } catch (Throwable $e) {
            Log::error('Stripe Delete Test Clock Error', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    public function advanceTimeOnTestClock(string $clockId, int $newTime): ?object
    {
        try {

            // Ensure newTime is a valid timestamp and is in the future based on the current frozen time
            $currentClock = $this->getTestClockById($clockId);
            if (! $currentClock) {
                Log::error('Stripe Advance Test Clock Error', ['error' => 'Clock not found']);

                return null;
            }

            $clock = $this->client->testHelpers->testClocks->advance($clockId, [
                'frozen_time' => $newTime,
            ]);

            return $clock;
        } catch (Throwable $e) {
            Log::error('Stripe Advance Test Clock Error', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function getCustomerCharges(string $stripeCustomerId, int $limit = 20): array
    {
        try {
            $charges = $this->client->charges->all([
                'customer' => $stripeCustomerId,
                'limit' => $limit,
            ]);

            return $charges->data;
        } catch (Throwable $e) {
            Log::error('Stripe Get Charges Error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function getCustomerInvoices(string $stripeCustomerId, int $limit = 20): array
    {
        try {
            $invoices = $this->client->invoices->all([
                'customer' => $stripeCustomerId,
                'limit' => $limit,
            ]);

            return $invoices->data;
        } catch (Throwable $e) {
            Log::error('Stripe Get Invoices Error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function getFormattedCustomerCharges(string $stripeCustomerId, int $limit = 20): array
    {
        try {
            $charges = $this->client->charges->all([
                'customer' => $stripeCustomerId,
                'limit' => $limit,
                'expand' => ['data.payment_method_details'], // optional but ensures complete data
            ]);

            return collect($charges->data)->map(function ($charge) {
                return [
                    'amount' => $charge->amount / 100,
                    'status' => $charge->status,
                    'payment_method_last4' => $charge->payment_method_details->card->last4 ?? 'N/A',
                    'description' => $charge->description ?? '',
                    'email' => $charge->billing_details->email ?? 'N/A',
                    'date' => \Carbon\Carbon::createFromTimestamp($charge->created),
                    'refunded_at' => $charge->refunded
                        ? \Carbon\Carbon::createFromTimestamp($charge->refunds->data[0]->created ?? $charge->created)
                        : null,
                    'decline_reason' => $charge->failure_message ?? null,
                ];
            })->toArray();
        } catch (Throwable $e) {
            Log::error('Stripe Get Formatted Charges Error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Report usage to Stripe for metered billing
     */
    public function reportUsage(array $data): void
    {
        try {
            $this->client->subscriptionItems->createUsageRecord($data['subscription_item'], [
                'quantity' => $data['quantity'],
                'timestamp' => $data['timestamp'] ?? time(),
                'action' => $data['action'] ?? 'increment',
            ]);
        } catch (Throwable $e) {
            Log::error('Stripe Usage Report Error', ['error' => $e->getMessage()]);

            throw $e;
        }
    }

    /**
     * Sync all products from Stripe to local database
     *
     * @param  array  $options  Sync options
     * @return array Sync results
     */
    public function syncProductsFromStripe(array $options = []): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        try {
            $params = [
                'limit' => $options['limit'] ?? 100,
                'active' => $options['active'] ?? true,
            ];

            $stripeProducts = $this->client->products->all($params);

            foreach ($stripeProducts->data as $stripeProduct) {
                try {
                    $localProduct = StripeProduct::where('product_id', $stripeProduct->id)->first();

                    $productData = [
                        'name' => $stripeProduct->name,
                        'description' => $stripeProduct->description,
                        'active' => $stripeProduct->active,
                        'metadata' => $stripeProduct->metadata ? (array) $stripeProduct->metadata : [],
                        'tax_code' => $stripeProduct->tax_code ?? 'txcd_99999999', // Default tax code if not provided
                        'images' => $stripeProduct->images ? (array) $stripeProduct->images : [],
                        'unit_label' => $stripeProduct->unit_label,
                        'url' => $stripeProduct->url,
                        'statement_descriptor' => $stripeProduct->statement_descriptor,
                        'product_id' => $stripeProduct->id,
                        'is_synced' => true,
                        'slug' => Str::slug($stripeProduct->name), // Generate slug from name
                    ];

                    if ($localProduct) {
                        // Update existing product
                        $localProduct->update($productData);
                        $results['updated']++;
                    } else {
                        // Create new product
                        StripeProduct::create($productData);
                        $results['created']++;
                    }
                } catch (Throwable $e) {
                    $results['errors'][] = [
                        'product_id' => $stripeProduct->id,
                        'error' => $e->getMessage(),
                    ];
                    Log::error('Error syncing product from Stripe', [
                        'product_id' => $stripeProduct->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (Throwable $e) {
            Log::error('Stripe Product Sync Error', ['error' => $e->getMessage()]);

            throw $e;
        }

        return $results;
    }

    /**
     * Sync all prices from Stripe to local database
     *
     * @param  array  $options  Sync options
     * @return array Sync results
     */
    public function syncPricesFromStripe(array $options = []): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        try {
            $params = [
                'limit' => $options['limit'] ?? 100,
                'active' => $options['active'] ?? true,
            ];

            $stripePrices = $this->client->prices->all($params);

            foreach ($stripePrices->data as $stripePrice) {
                try {
                    $localPrice = StripePrice::where('price_id', $stripePrice->id)->first();

                    // Find the local product by Stripe product ID
                    $localProduct = StripeProduct::where('product_id', $stripePrice->product)->first();

                    if (! $localProduct) {
                        $results['skipped']++;

                        continue; // Skip if we don't have the product locally
                    }

                    $priceData = [
                        'product_id' => $localProduct->id, // Use local product ID
                        'price_id' => $stripePrice->id,
                        'active' => $stripePrice->active,
                        'currency' => $stripePrice->currency,
                        'unit_amount' => $stripePrice->unit_amount,
                        'nickname' => $stripePrice->nickname,
                        'metadata' => $stripePrice->metadata ? (array) $stripePrice->metadata : [],
                        'type' => $stripePrice->type,
                        'billing_scheme' => $stripePrice->billing_scheme,
                        'tiers_mode' => $stripePrice->tiers_mode,
                        'tiers' => $stripePrice->tiers ? (array) $stripePrice->tiers : [],
                        'recurring' => $stripePrice->recurring ? (array) $stripePrice->recurring : [],
                        'tax_behavior' => $stripePrice->tax_behavior,
                        'lookup_key' => $stripePrice->lookup_key,
                        'is_synced' => true,
                    ];

                    if ($localPrice) {
                        // Update existing price
                        $localPrice->update($priceData);
                        $results['updated']++;
                    } else {
                        // Create new price
                        StripePrice::create($priceData);
                        $results['created']++;
                    }
                } catch (Throwable $e) {
                    $results['errors'][] = [
                        'price_id' => $stripePrice->id,
                        'error' => $e->getMessage(),
                    ];
                    Log::error('Error syncing price from Stripe', [
                        'price_id' => $stripePrice->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (Throwable $e) {
            Log::error('Stripe Price Sync Error', ['error' => $e->getMessage()]);

            throw $e;
        }

        return $results;
    }

    /**
     * Sync usage meters from Stripe to local database
     *
     * @param  array  $options  Sync options
     * @return array Sync results
     */
    public function syncUsageMetersFromStripe(array $options = []): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        try {
            // Get all subscriptions first, then their subscription items
            $subscriptionParams = [
                'limit' => $options['limit'] ?? 100,
                'expand' => ['data.items.data.price'],
            ];

            $subscriptions = $this->client->subscriptions->all($subscriptionParams);

            foreach ($subscriptions->data as $subscription) {
                foreach ($subscription->items->data as $subscriptionItem) {
                    try {
                        // Find the local product by Stripe product ID
                        $localProduct = StripeProduct::where('product_id', $subscriptionItem->price->product)->first();

                        if (! $localProduct) {
                            $results['skipped']++;

                            continue; // Skip if we don't have the product locally
                        }

                        // Check if usage meter exists locally
                        $localMeter = StripeMeter::where('stripe_subscription_item_id', $subscriptionItem->id)->first();

                        $meterData = [
                            'product_id' => $localProduct->id,
                            'stripe_subscription_item_id' => $subscriptionItem->id,
                            'stripe_price_id' => $subscriptionItem->price->id,
                            'quantity' => $subscriptionItem->quantity,
                            'usage_type' => $subscriptionItem->price->recurring->usage_type ?? 'licensed',
                            'is_active' => true,
                        ];

                        if ($localMeter) {
                            // Update existing meter
                            $localMeter->update($meterData);
                            $results['updated']++;
                        } else {
                            // Create new meter
                            StripeMeter::create($meterData);
                            $results['created']++;
                        }
                    } catch (Throwable $e) {
                        $results['errors'][] = [
                            'subscription_item_id' => $subscriptionItem->id,
                            'error' => $e->getMessage(),
                        ];
                        Log::error('Error syncing usage meter from Stripe', [
                            'subscription_item_id' => $subscriptionItem->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        } catch (Throwable $e) {
            Log::error('Stripe Usage Meter Sync Error', ['error' => $e->getMessage()]);

            throw $e;
        }

        return $results;
    }

    /**
     * Sync all data from Stripe (products, prices, usage meters)
     *
     * @param  array  $options  Sync options
     * @return array Sync results
     */
    public function syncAllFromStripe(array $options = []): array
    {
        $results = [
            'products' => [],
            'prices' => [],
            'usage_meters' => [],
            'summary' => [
                'total_created' => 0,
                'total_updated' => 0,
                'total_skipped' => 0,
                'total_errors' => 0,
            ],
        ];

        try {
            // Sync products first (prices depend on products)
            $results['products'] = $this->syncProductsFromStripe($options);

            // Sync prices
            $results['prices'] = $this->syncPricesFromStripe($options);

            // Sync usage meters
            $results['usage_meters'] = $this->syncUsageMetersFromStripe($options);

            // Calculate summary
            foreach (['products', 'prices', 'usage_meters'] as $type) {
                $results['summary']['total_created'] += $results[$type]['created'];
                $results['summary']['total_updated'] += $results[$type]['updated'];
                $results['summary']['total_skipped'] += $results[$type]['skipped'];
                $results['summary']['total_errors'] += count($results[$type]['errors']);
            }

            Log::info('Stripe sync completed', $results['summary']);

        } catch (Throwable $e) {
            Log::error('Stripe Full Sync Error', ['error' => $e->getMessage()]);

            throw $e;
        }

        return $results;
    }
}
