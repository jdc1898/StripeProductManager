<?php

namespace Fullstack\StripeProductManager\Console\Commands\Stripe;

use App\Services\Stripe\StripeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FetchStripePrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:fetch-prices
                            {--limit=100 : Number of prices to fetch}
                            {--save : Save prices to the stripe_prices table}
                            {--product-id= : Filter by specific product ID}
                            {--price-id= : Fetch a specific price by Stripe price ID}
                            {--type= : Filter by price type (one_time/recurring)}
                            {--currency= : Filter by currency (e.g., usd, eur)}
                            {--include-inactive : Include inactive prices}
                            {--dashboard-only : Only show prices visible in Stripe dashboard}
                            {--detailed : Show more detailed information}
                            {--raw : Show raw API response data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all active prices from Stripe and display them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $save = $this->option('save');
        $productId = $this->option('product-id');
        $priceId = $this->option('price-id');
        $type = $this->option('type');
        $currency = $this->option('currency');
        $includeInactive = $this->option('include-inactive');
        $dashboardOnly = $this->option('dashboard-only');
        $detailed = $this->option('detailed');
        $raw = $this->option('raw');

        $this->info("Fetching up to $limit prices from Stripe...");

        if ($priceId) {
            $this->info("Fetching specific price ID: $priceId");
        }
        if ($productId) {
            $this->info("Filtering by product ID: $productId");
        }
        if ($type) {
            $this->info("Filtering by type: $type");
        }
        if ($currency) {
            $this->info("Filtering by currency: $currency");
        }
        if ($includeInactive) {
            $this->info('Including inactive prices');
        }
        if ($dashboardOnly) {
            $this->info('Only showing dashboard-visible prices');
        }

        $stripeService = app(StripeService::class);
        $allPrices = [];

        // If a specific price ID is provided, fetch just that price
        if ($priceId) {
            try {
                $params = [];
                if ($raw || $save) {
                    $params['expand'] = ['tiers'];
                }
                $price = $stripeService->getClient()->prices->retrieve($priceId, $params);
                $allPrices = [$price];
                $this->info("Successfully fetched price: $priceId");
            } catch (\Exception $e) {
                $this->error("Failed to fetch price $priceId: ".$e->getMessage());

                return 1;
            }
        } else {
            // Original logic for fetching multiple prices
            $hasMore = true;
            $startingAfter = null;
            $fetched = 0;

            while ($hasMore && $fetched < $limit) {
                $params = [
                    'limit' => min(100, $limit - $fetched), // Stripe max is 100 per request
                ];

                // Add expand option for raw data
                if ($raw || $save) {
                    $params['expand'] = ['data.tiers'];
                }

                // Only filter by active if not including inactive
                if (! $includeInactive) {
                    $params['active'] = true;
                }

                if ($startingAfter) {
                    $params['starting_after'] = $startingAfter;
                }

                // Add filters
                if ($productId) {
                    $params['product'] = $productId;
                }
                if ($type) {
                    $params['type'] = $type;
                }

                $prices = $stripeService->getClient()->prices->all($params);

                if (empty($prices->data)) {
                    $hasMore = false;

                    break;
                }

                // Filter by currency if specified (since Stripe API doesn't support this filter)
                $filteredPrices = $prices->data;
                if ($currency) {
                    $filteredPrices = array_filter($prices->data, function ($price) use ($currency) {
                        return strtolower($price->currency) === strtolower($currency);
                    });
                }

                // Filter for dashboard-visible prices only
                if ($dashboardOnly) {
                    $filteredPrices = array_filter($filteredPrices, function ($price) {
                        // Only show prices that are active and have meaningful data
                        return $price->active &&
                               ($price->nickname || $price->unit_amount > 0) &&
                               ! empty($price->product);
                    });
                }

                $allPrices = array_merge($allPrices, $filteredPrices);
                $fetched += count($filteredPrices);

                // Check if there are more pages
                $hasMore = $prices->has_more;
                if ($hasMore && ! empty($prices->data)) {
                    $startingAfter = end($prices->data)->id;
                }

                $this->info("Fetched $fetched prices so far...");
            }
        }

        if (empty($allPrices)) {
            $this->warn('No active prices found in Stripe matching your criteria.');

            return 0;
        }

        // Show raw API response if requested
        if ($raw) {
            $this->info('=== RAW API RESPONSE ===');
            foreach ($allPrices as $index => $price) {
                $this->line('Price #'.($index + 1).':');
                $this->line(json_encode($price, JSON_PRETTY_PRINT));
                $this->line('');
            }
            $this->info('=== END RAW API RESPONSE ===');
            $this->line('');
        }

        if ($detailed) {
            $rows = array_map(function ($price) {
                return [
                    $price->id,
                    $price->product,
                    $price->currency,
                    $price->unit_amount ? number_format($price->unit_amount / 100, 2) : 'N/A',
                    $price->type,
                    $price->active ? 'Yes' : 'No',
                    date('Y-m-d H:i:s', $price->created),
                    $price->nickname,
                    $price->billing_scheme,
                    $price->recurring ? json_encode($price->recurring) : 'N/A',
                    $price->tiers ? json_encode($price->tiers) : 'N/A',
                    $price->metadata ? json_encode($price->metadata) : '{}',
                ];
            }, $allPrices);

            $this->table(
                ['ID', 'Product', 'Currency', 'Amount', 'Type', 'Active', 'Created', 'Nickname', 'Billing Scheme', 'Recurring', 'Tiers', 'Metadata'],
                $rows
            );
        } else {
            $rows = array_map(function ($price) {
                return [
                    $price->id,
                    $price->product,
                    $price->currency,
                    $price->unit_amount ? number_format($price->unit_amount / 100, 2) : 'N/A',
                    $price->type,
                    $price->active ? 'Yes' : 'No',
                    date('Y-m-d H:i:s', $price->created),
                    $price->nickname,
                ];
            }, $allPrices);

            $this->table(
                ['ID', 'Product', 'Currency', 'Amount', 'Type', 'Active', 'Created', 'Nickname'],
                $rows
            );
        }

        if ($save) {
            $this->info('Saving prices to the stripe_prices table...');
            $created = 0;
            $updated = 0;

            // Temporarily disable foreign key checks to handle circular dependency
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($allPrices as $price) {

                $data = [
                    'stripe_id' => $price->id,
                    'active' => $price->active,
                    'billing_scheme' => $price->billing_scheme,
                    'created' => $price->created,
                    'currency' => $price->currency,
                    'custom_unit_amount' => $price->custom_unit_amount,
                    'livemode' => $price->livemode,
                    'lookup_key' => $price->lookup_key,
                    'metadata' => $price->metadata,
                    'nickname' => $price->nickname,
                    'product' => $price->product,
                    'recurring' => $price->recurring,
                    'tax_behavior' => $price->tax_behavior,
                    'tiers_mode' => $price->tiers_mode,
                    'tiers' => $price->tiers,
                    'transform_quantity' => $price->transform_quantity,
                    'type' => $price->type,
                    'unit_amount' => $price->unit_amount,
                    'unit_amount_decimal' => $price->unit_amount_decimal,
                ];
                $model = \App\Models\StripePrice::updateOrCreate(
                    ['stripe_id' => $price->id],
                    $data
                );
                if ($model->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info("Saved prices: Created $created, Updated $updated");
        }

        $this->info('Total prices fetched: '.count($allPrices));
        $this->info('Done!');

        return 0;
    }
}
