<?php

namespace Fullstack\StripeProductManager\Console\Commands\Stripe;

use App\Services\Stripe\StripeService;
use Illuminate\Console\Command;

class FetchStripeTaxCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:fetch-tax-codes {--limit=100 : Number of tax codes to fetch} {--save : Save tax codes to the stripe_tax_codes table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all tax codes from Stripe and display them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $save = $this->option('save');
        $this->info("Fetching up to $limit tax codes from Stripe...");

        $stripeService = app(StripeService::class);
        $allTaxCodes = [];
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

            $taxCodes = $stripeService->getClient()->taxCodes->all($params);

            if (empty($taxCodes->data)) {
                $hasMore = false;

                break;
            }

            $allTaxCodes = array_merge($allTaxCodes, $taxCodes->data);
            $fetched += count($taxCodes->data);

            // Check if there are more pages
            $hasMore = $taxCodes->has_more;
            if ($hasMore && ! empty($taxCodes->data)) {
                $startingAfter = end($taxCodes->data)->id;
            }

            $this->info("Fetched $fetched tax codes so far...");
        }

        if (empty($allTaxCodes)) {
            $this->warn('No tax codes found in Stripe.');

            return 0;
        }

        $rows = array_map(function ($taxCode) {
            return [
                $taxCode->id,
                $taxCode->name ?? 'N/A',
                $taxCode->description ? substr($taxCode->description, 0, 50).'...' : 'N/A',
            ];
        }, $allTaxCodes);

        $this->table(
            ['ID', 'Name', 'Description'],
            $rows
        );

        if ($save) {
            $this->info('Saving tax codes to the stripe_tax_codes table...');
            $created = 0;
            $updated = 0;

            foreach ($allTaxCodes as $taxCode) {
                $data = [
                    'stripe_id' => $taxCode->id,
                    'object' => $taxCode->object,
                    'description' => $taxCode->description,
                    'name' => $taxCode->name,
                ];

                $model = \App\Models\StripeTaxCode::updateOrCreate(
                    ['stripe_id' => $taxCode->id],
                    $data
                );
                if ($model->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            }

            $this->info("Saved tax codes: Created $created, Updated $updated");
        }

        $this->info('Total tax codes fetched: '.count($allTaxCodes));
        $this->info('Done!');

        return 0;
    }
}
