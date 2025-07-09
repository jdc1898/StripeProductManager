<?php

namespace Fullstack\StripeProductManager\Console\Commands\Stripe;

use Illuminate\Console\Command;
use App\Services\Stripe\StripeService;

class FetchStripeMeters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:fetch-meters
                            {--limit=100 : Number of meters to fetch}
                            {--save : Save meters to the stripe_meters table}
                            {--status= : Filter by status (active/inactive)}
                            {--include-inactive : Include inactive meters}
                            {--detailed : Show more detailed information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all meters from Stripe and display them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $save = $this->option('save');
        $status = $this->option('status');
        $includeInactive = $this->option('include-inactive');
        $verbose = $this->option('detailed');

        $this->info("Fetching up to $limit meters from Stripe...");

        if ($status) {
            $this->info("Filtering by status: $status");
        }
        if ($includeInactive) {
            $this->info("Including inactive meters");
        }

        $stripeService = app(StripeService::class);
        $allMeters = [];
        $hasMore = true;
        $startingAfter = null;
        $fetched = 0;

        while ($hasMore && $fetched < $limit) {
            $params = [
                'limit' => min(100, $limit - $fetched), // Stripe max is 100 per request
            ];

            // Only filter by active status if not including inactive
            if (!$includeInactive && !$status) {
                $params['status'] = 'active';
            } elseif ($status) {
                $params['status'] = $status;
            }

            if ($startingAfter) {
                $params['starting_after'] = $startingAfter;
            }

            try {
                $meters = $stripeService->getClient()->billing->meters->all($params);
            } catch (\Exception $e) {
                $this->error("Error fetching meters: " . $e->getMessage());
                $this->error("This might be because billing meters are only available in certain Stripe accounts or API versions.");
                return 1;
            }

            if (empty($meters->data)) {
                $hasMore = false;
                break;
            }

            $allMeters = array_merge($allMeters, $meters->data);
            $fetched += count($meters->data);

            // Check if there are more pages
            $hasMore = $meters->has_more;
            if ($hasMore && !empty($meters->data)) {
                $startingAfter = end($meters->data)->id;
            }

            $this->info("Fetched $fetched meters so far...");
        }

        if (empty($allMeters)) {
            $this->warn('No meters found in Stripe matching your criteria.');
            return 0;
        }

        if ($verbose) {
            $rows = array_map(function ($meter) {
                return [
                    $meter->id,
                    $meter->display_name,
                    $meter->event_name,
                    $meter->status,
                    $meter->livemode ? 'Yes' : 'No',
                    date('Y-m-d H:i:s', $meter->created),
                    date('Y-m-d H:i:s', $meter->updated),
                    $meter->customer_mapping ? json_encode($meter->customer_mapping) : 'N/A',
                    $meter->default_aggregation ? json_encode($meter->default_aggregation) : 'N/A',
                    $meter->value_settings ? json_encode($meter->value_settings) : 'N/A',
                ];
            }, $allMeters);

            $this->table(
                ['ID', 'Display Name', 'Event Name', 'Status', 'Live Mode', 'Created', 'Updated', 'Customer Mapping', 'Default Aggregation', 'Value Settings'],
                $rows
            );
        } else {
            $rows = array_map(function ($meter) {
                return [
                    $meter->id,
                    $meter->display_name,
                    $meter->event_name,
                    $meter->status,
                    $meter->livemode ? 'Yes' : 'No',
                    date('Y-m-d H:i:s', $meter->created),
                    date('Y-m-d H:i:s', $meter->updated),
                ];
            }, $allMeters);

            $this->table(
                ['ID', 'Display Name', 'Event Name', 'Status', 'Live Mode', 'Created', 'Updated'],
                $rows
            );
        }

        if ($save) {
            $this->info('Saving meters to the stripe_meters table...');
            $created = 0;
            $updated = 0;
            foreach ($allMeters as $meter) {
                $data = [
                    'stripe_id' => $meter->id,
                    'created' => $meter->created,
                    'customer_mapping' => $meter->customer_mapping,
                    'default_aggregation' => $meter->default_aggregation,
                    'display_name' => $meter->display_name,
                    'event_name' => $meter->event_name,
                    'event_time_window' => $meter->event_time_window,
                    'livemode' => $meter->livemode,
                    'status' => $meter->status,
                    'status_transitions' => $meter->status_transitions,
                    'updated' => $meter->updated,
                    'value_settings' => $meter->value_settings,
                ];
                $model = \App\Models\StripeMeter::updateOrCreate(
                    ['stripe_id' => $meter->id],
                    $data
                );
                if ($model->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            }
            $this->info("Saved meters: Created $created, Updated $updated");
        }

        $this->info("Total meters fetched: " . count($allMeters));
        $this->info('Done!');
        return 0;
    }
}
