<?php

namespace Fullstack\StripeProductManager\Console\Commands\Stripe;

use App\Services\Stripe\StripeService;
use Illuminate\Console\Command;

class FetchStripeDiscounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:fetch-discounts {--limit=100 : Number of discounts to fetch} {--save : Save discounts to the stripe_discounts table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all discounts from Stripe customers and subscriptions and display them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $save = $this->option('save');
        $this->info("Fetching up to $limit discounts from Stripe customers and subscriptions...");

        $stripeService = app(StripeService::class);
        $allDiscounts = [];
        $fetched = 0;

        // Fetch discounts from customers
        $this->info('Fetching discounts from customers...');
        $customers = $stripeService->getClient()->customers->all(['limit' => min(100, $limit)]);

        foreach ($customers->data as $customer) {
            if ($customer->discount && $fetched < $limit) {
                $discount = $customer->discount;
                $discount->customer = $customer->id; // Add customer ID to the discount object
                $allDiscounts[] = $discount;
                $fetched++;
            }
        }

        // Fetch discounts from subscriptions
        $this->info('Fetching discounts from subscriptions...');
        $subscriptions = $stripeService->getClient()->subscriptions->all(['limit' => min(100, $limit)]);

        foreach ($subscriptions->data as $subscription) {
            if ($subscription->discount && $fetched < $limit) {
                $discount = $subscription->discount;
                $discount->subscription = $subscription->id; // Add subscription ID to the discount object
                $allDiscounts[] = $discount;
                $fetched++;
            }
        }

        if (empty($allDiscounts)) {
            $this->warn('No discounts found in Stripe customers or subscriptions.');

            return 0;
        }

        $rows = array_map(function ($discount) {
            return [
                $discount->id ?? 'N/A',
                $discount->customer ?? 'N/A',
                $discount->subscription ?? 'N/A',
                $discount->start ? date('Y-m-d H:i:s', $discount->start) : 'N/A',
                $discount->end ? date('Y-m-d H:i:s', $discount->end) : 'N/A',
                $discount->coupon ? $discount->coupon->id : 'N/A',
            ];
        }, $allDiscounts);

        $this->table(
            ['ID', 'Customer', 'Subscription', 'Start', 'End', 'Coupon'],
            $rows
        );

        if ($save) {
            $this->info('Saving discounts to the stripe_discounts table...');
            $created = 0;
            $updated = 0;

            foreach ($allDiscounts as $discount) {
                $data = [
                    'stripe_id' => $discount->id ?? uniqid('disc_'),
                    'object' => $discount->object ?? 'discount',
                    'checkout_session' => $discount->checkout_session ?? null,
                    'coupon' => $discount->coupon ? $discount->coupon->id : null,
                    'customer' => $discount->customer ?? null,
                    'end' => $discount->end ? date('Y-m-d H:i:s', $discount->end) : null,
                    'invoice' => $discount->invoice ?? null,
                    'invoice_item' => $discount->invoice_item ?? null,
                    'promotion_code' => $discount->promotion_code ?? null,
                    'start' => $discount->start ? date('Y-m-d H:i:s', $discount->start) : null,
                    'subscription' => $discount->subscription ?? null,
                ];

                $model = \App\Models\StripeDiscount::updateOrCreate(
                    ['stripe_id' => $data['stripe_id']],
                    $data
                );
                if ($model->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            }

            $this->info("Saved discounts: Created $created, Updated $updated");
        }

        $this->info('Total discounts fetched: '.count($allDiscounts));
        $this->info('Done!');

        return 0;
    }
}
