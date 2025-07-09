<?php

namespace Fullstack\StripeProductManager\Console\Commands\Stripe;

use Illuminate\Console\Command;
use App\Services\Stripe\StripeService;
use Illuminate\Support\Facades\DB;

class FetchStripeProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:fetch-products {--limit=100 : Number of products to fetch} {--save : Save products to the stripe_products table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all active products from Stripe and display them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $save = $this->option('save');
        $this->info("Fetching up to $limit active products from Stripe...");

        $stripeService = app(StripeService::class);
        $allProducts = [];
        $hasMore = true;
        $startingAfter = null;
        $fetched = 0;

        while ($hasMore && $fetched < $limit) {
            $params = [
                'limit' => min(100, $limit - $fetched), // Stripe max is 100 per request
            ];

            if ($startingAfter) {
                $params['starting_after'] = $startingAfter;
            }

            $products = $stripeService->getClient()->products->all($params);

            if (empty($products->data)) {
                $hasMore = false;
                break;
            }

            $allProducts = array_merge($allProducts, $products->data);
            $fetched += count($products->data);

            // Check if there are more pages
            $hasMore = $products->has_more;
            if ($hasMore && !empty($products->data)) {
                $startingAfter = end($products->data)->id;
            }

            $this->info("Fetched $fetched products so far...");
        }

        if (empty($allProducts)) {
            $this->warn('No active products found in Stripe.');
            return 0;
        }

        $rows = array_map(function ($product) {
            return [
                $product->id,
                $product->name,
                $product->active ? 'Yes' : 'No',
                date('Y-m-d H:i:s', $product->created),
                $product->description,
            ];
        }, $allProducts);

        $this->table(
            ['ID', 'Name', 'Active', 'Created', 'Description'],
            $rows
        );

        if ($save) {
            $this->info('Saving products to the stripe_products table...');
            $created = 0;
            $updated = 0;

            // Temporarily disable foreign key checks to handle circular dependency
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($allProducts as $product) {
                $data = [
                    'stripe_id' => $product->id,
                    'active' => $product->active,
                    'created' => $product->created,
                    'default_price' => $product->default_price, // Save the actual default_price
                    'description' => $product->description,
                    'images' => $product->images,
                    'marketing_features' => $product->marketing_features ?? [],
                    'livemode' => $product->livemode,
                    'metadata' => $product->metadata,
                    'name' => $product->name,
                    'package_dimensions' => $product->package_dimensions,
                    'shippable' => $product->shippable,
                    'statement_descriptor' => $product->statement_descriptor,
                    'tax_code' => $product->tax_code,
                    'unit_label' => $product->unit_label,
                    'updated' => $product->updated,
                    'url' => $product->url,
                ];
                $model = \App\Models\StripeProduct::updateOrCreate(
                    ['stripe_id' => $product->id],
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

            $this->info("Saved products: Created $created, Updated $updated");
        }

        $this->info("Total products fetched: " . count($allProducts));
        $this->info('Done!');
        return 0;
    }
}
