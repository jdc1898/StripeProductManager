<?php

namespace Fullstack\StripeProductManager\Console\Commands\Stripe;

use Illuminate\Console\Command;
use App\Services\Stripe\StripeService;
use Illuminate\Support\Facades\DB;

class FetchStripeCoupons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:fetch-coupons {--limit=100 : Number of coupons to fetch} {--save : Save coupons to the stripe_coupons table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all coupons from Stripe and display them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $save = $this->option('save');
        $this->info("Fetching up to $limit coupons from Stripe...");

        $stripeService = app(StripeService::class);
        $allCoupons = [];
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

            $coupons = $stripeService->getClient()->coupons->all($params);

            if (empty($coupons->data)) {
                $hasMore = false;
                break;
            }

            $allCoupons = array_merge($allCoupons, $coupons->data);
            $fetched += count($coupons->data);

            // Check if there are more pages
            $hasMore = $coupons->has_more;
            if ($hasMore && !empty($coupons->data)) {
                $startingAfter = end($coupons->data)->id;
            }

            $this->info("Fetched $fetched coupons so far...");
        }

        if (empty($allCoupons)) {
            $this->warn('No coupons found in Stripe.');
            return 0;
        }

        $rows = array_map(function ($coupon) {
            $discount = '';
            if ($coupon->percent_off) {
                $discount = $coupon->percent_off . '%';
            } elseif ($coupon->amount_off) {
                $currency = $coupon->currency ?? 'usd';
                $amount = number_format($coupon->amount_off / 100, 2);
                $discount = '$' . $amount . ' ' . strtoupper($currency);
            } else {
                $discount = 'N/A';
            }

            return [
                $coupon->id,
                $coupon->name ?? 'N/A',
                $discount,
                $coupon->duration,
                $coupon->duration_in_months ?? 'N/A',
                $coupon->times_redeemed,
                $coupon->max_redemptions ?? 'Unlimited',
                $coupon->valid ? 'Yes' : 'No',
            ];
        }, $allCoupons);

        $this->table(
            ['ID', 'Name', 'Discount', 'Duration', 'Months', 'Redeemed', 'Max Redemptions', 'Valid'],
            $rows
        );

        if ($save) {
            $this->info('Saving coupons to the stripe_coupons table...');
            $created = 0;
            $updated = 0;

            foreach ($allCoupons as $coupon) {
                $data = [
                    'stripe_id' => $coupon->id,
                    'object' => $coupon->object,
                    'amount_off' => $coupon->amount_off,
                    'created' => $coupon->created,
                    'currency' => $coupon->currency,
                    'duration' => $coupon->duration,
                    'duration_in_months' => $coupon->duration_in_months,
                    'livemode' => $coupon->livemode,
                    'max_redemptions' => $coupon->max_redemptions,
                    'metadata' => $coupon->metadata,
                    'name' => $coupon->name,
                    'percent_off' => $coupon->percent_off,
                    'redeem_by' => $coupon->redeem_by,
                    'times_redeemed' => $coupon->times_redeemed,
                    'valid' => $coupon->valid,
                ];

                $model = \App\Models\StripeCoupon::updateOrCreate(
                    ['stripe_id' => $coupon->id],
                    $data
                );
                if ($model->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            }

            $this->info("Saved coupons: Created $created, Updated $updated");
        }

        $this->info("Total coupons fetched: " . count($allCoupons));
        $this->info('Done!');
        return 0;
    }
}
