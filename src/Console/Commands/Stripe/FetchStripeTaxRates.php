<?php

namespace Fullstack\StripeProductManager\Console\Commands\Stripe;

use App\Services\Stripe\StripeService;
use Illuminate\Console\Command;

class FetchStripeTaxRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:fetch-tax-rates {--limit=100 : Number of tax rates to fetch} {--save : Save tax rates to the stripe_tax_rates table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all tax rates from Stripe and display them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $save = $this->option('save');
        $this->info("Fetching up to $limit tax rates from Stripe...");

        $stripeService = app(StripeService::class);
        $allTaxRates = [];
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

            $taxRates = $stripeService->getClient()->taxRates->all($params);

            if (empty($taxRates->data)) {
                $hasMore = false;

                break;
            }

            $allTaxRates = array_merge($allTaxRates, $taxRates->data);
            $fetched += count($taxRates->data);

            // Check if there are more pages
            $hasMore = $taxRates->has_more;
            if ($hasMore && ! empty($taxRates->data)) {
                $startingAfter = end($taxRates->data)->id;
            }

            $this->info("Fetched $fetched tax rates so far...");
        }

        if (empty($allTaxRates)) {
            $this->warn('No tax rates found in Stripe.');

            return 0;
        }

        $rows = array_map(function ($taxRate) {
            return [
                $taxRate->id,
                $taxRate->display_name ?? 'N/A',
                $taxRate->percentage ? $taxRate->percentage.'%' : 'N/A',
                $taxRate->country ?? 'Global',
                $taxRate->state ?? 'N/A',
                $taxRate->jurisdiction ?? 'N/A',
                $taxRate->inclusive ? 'Yes' : 'No',
                $taxRate->active ? 'Yes' : 'No',
            ];
        }, $allTaxRates);

        $this->table(
            ['ID', 'Display Name', 'Percentage', 'Country', 'State', 'Jurisdiction', 'Inclusive', 'Active'],
            $rows
        );

        if ($save) {
            $this->info('Saving tax rates to the stripe_tax_rates table...');
            $created = 0;
            $updated = 0;

            foreach ($allTaxRates as $taxRate) {
                $data = [
                    'stripe_id' => $taxRate->id,
                    'object' => $taxRate->object,
                    'active' => $taxRate->active,
                    'country' => $taxRate->country,
                    'created' => $taxRate->created,
                    'description' => $taxRate->description,
                    'display_name' => $taxRate->display_name,
                    'inclusive' => $taxRate->inclusive,
                    'jurisdiction' => $taxRate->jurisdiction,
                    'livemode' => $taxRate->livemode,
                    'metadata' => $taxRate->metadata,
                    'percentage' => $taxRate->percentage,
                    'state' => $taxRate->state,
                    'tax_type' => $taxRate->tax_type,
                ];

                $model = \App\Models\StripeTaxRate::updateOrCreate(
                    ['stripe_id' => $taxRate->id],
                    $data
                );
                if ($model->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            }

            $this->info("Saved tax rates: Created $created, Updated $updated");
        }

        $this->info('Total tax rates fetched: '.count($allTaxRates));
        $this->info('Done!');

        return 0;
    }
}
