<?php

namespace Fullstack\StripeProductManager\Console\Commands\Stripe;

use Illuminate\Console\Command;
use App\Services\Stripe\StripeService;
use Illuminate\Support\Facades\DB;

class FetchStripePromotionCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:fetch-promotion-codes {--limit=100 : Number of promotion codes to fetch} {--save : Save promotion codes to the stripe_promotion_codes table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all promotion codes from Stripe and display them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $save = $this->option('save');
        $this->info("Fetching up to $limit promotion codes from Stripe...");

        $stripeService = app(StripeService::class);
        $allPromotionCodes = [];
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

            $promotionCodes = $stripeService->getClient()->promotionCodes->all($params);

            if (empty($promotionCodes->data)) {
                $hasMore = false;
                break;
            }

            $allPromotionCodes = array_merge($allPromotionCodes, $promotionCodes->data);
            $fetched += count($promotionCodes->data);

            // Check if there are more pages
            $hasMore = $promotionCodes->has_more;
            if ($hasMore && !empty($promotionCodes->data)) {
                $startingAfter = end($promotionCodes->data)->id;
            }

            $this->info("Fetched $fetched promotion codes so far...");
        }

        if (empty($allPromotionCodes)) {
            $this->warn('No promotion codes found in Stripe.');
            return 0;
        }

        $rows = array_map(function ($promotionCode) {
            return [
                $promotionCode->id,
                $promotionCode->code,
                $promotionCode->active ? 'Yes' : 'No',
                $promotionCode->coupon ? $promotionCode->coupon->id : 'N/A',
                $promotionCode->times_redeemed,
                $promotionCode->max_redemptions ?? 'Unlimited',
                $promotionCode->expires_at ? date('Y-m-d H:i:s', $promotionCode->expires_at) : 'Never',
            ];
        }, $allPromotionCodes);

        $this->table(
            ['ID', 'Code', 'Active', 'Coupon', 'Redeemed', 'Max Redemptions', 'Expires'],
            $rows
        );

        if ($save) {
            $this->info('Saving promotion codes to the stripe_promotion_codes table...');
            $created = 0;
            $updated = 0;

            foreach ($allPromotionCodes as $promotionCode) {
                $data = [
                    'stripe_id' => $promotionCode->id,
                    'object' => $promotionCode->object,
                    'active' => $promotionCode->active,
                    'code' => $promotionCode->code,
                    'coupon' => $promotionCode->coupon ? $promotionCode->coupon->id : null,
                    'created' => $promotionCode->created,
                    'customer' => $promotionCode->customer,
                    'expires_at' => $promotionCode->expires_at,
                    'livemode' => $promotionCode->livemode,
                    'max_redemptions' => $promotionCode->max_redemptions,
                    'metadata' => $promotionCode->metadata,
                    'restrictions' => $promotionCode->restrictions,
                    'times_redeemed' => $promotionCode->times_redeemed,
                ];

                $model = \App\Models\StripePromotionCode::updateOrCreate(
                    ['stripe_id' => $promotionCode->id],
                    $data
                );
                if ($model->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            }

            $this->info("Saved promotion codes: Created $created, Updated $updated");
        }

        $this->info("Total promotion codes fetched: " . count($allPromotionCodes));
        $this->info('Done!');
        return 0;
    }
}
