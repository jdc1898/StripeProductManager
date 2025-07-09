<?php

namespace Fullstack\StripeProductManager\Console\Commands\Stripe;

use Illuminate\Console\Command;
use App\Services\Stripe\StripeService;

class FetchStripeCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:fetch-customers
                            {--limit=100 : Number of customers to fetch}
                            {--save : Save customers to the stripe_customers table}
                            {--email= : Filter by email address}
                            {--created= : Filter by creation date (e.g., 2023-01-01)}
                            {--include-deleted : Include deleted customers}
                            {--no-link-users : Do not automatically link customers to users by email}
                            {--detailed : Show more detailed information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all customers from Stripe and display them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $save = $this->option('save');
        $email = $this->option('email');
        $created = $this->option('created');
        $includeDeleted = $this->option('include-deleted');
        $detailed = $this->option('detailed');
        $noLinkUsers = $this->option('no-link-users');

        $this->info("Fetching up to $limit customers from Stripe...");

        if ($email) {
            $this->info("Filtering by email: $email");
        }
        if ($created) {
            $this->info("Filtering by creation date: $created");
        }
        if ($includeDeleted) {
            $this->info("Including deleted customers");
        }

        $stripeService = app(StripeService::class);
        $allCustomers = [];
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

            // Add filters
            if ($email) {
                $params['email'] = $email;
            }
            if ($created) {
                $params['created'] = [
                    'gte' => strtotime($created)
                ];
            }

            $customers = $stripeService->getClient()->customers->all($params);

            if (empty($customers->data)) {
                $hasMore = false;
                break;
            }

            // Filter out deleted customers unless specifically requested
            $filteredCustomers = $customers->data;
            if (!$includeDeleted) {
                $filteredCustomers = array_filter($customers->data, function($customer) {
                    return !$customer->deleted;
                });
            }

            $allCustomers = array_merge($allCustomers, $filteredCustomers);
            $fetched += count($filteredCustomers);

            // Check if there are more pages
            $hasMore = $customers->has_more;
            if ($hasMore && !empty($customers->data)) {
                $startingAfter = end($customers->data)->id;
            }

            $this->info("Fetched $fetched customers so far...");
        }

        if (empty($allCustomers)) {
            $this->warn('No customers found in Stripe matching your criteria.');
            return 0;
        }

        if ($detailed) {
            $rows = array_map(function ($customer) {
                return [
                    $customer->id,
                    $customer->name,
                    $customer->email,
                    $customer->currency,
                    $customer->balance / 100, // Convert from cents
                    $customer->delinquent ? 'Yes' : 'No',
                    $customer->livemode ? 'Yes' : 'No',
                    date('Y-m-d H:i:s', $customer->created),
                    $customer->description,
                    $customer->phone,
                    $customer->tax_exempt,
                    $customer->metadata ? json_encode($customer->metadata) : '{}',
                ];
            }, $allCustomers);

            $this->table(
                ['ID', 'Name', 'Email', 'Currency', 'Balance', 'Delinquent', 'Live Mode', 'Created', 'Description', 'Phone', 'Tax Exempt', 'Metadata'],
                $rows
            );
        } else {
            $rows = array_map(function ($customer) {
                return [
                    $customer->id,
                    $customer->name,
                    $customer->email,
                    $customer->currency,
                    $customer->balance / 100, // Convert from cents
                    $customer->delinquent ? 'Yes' : 'No',
                    $customer->livemode ? 'Yes' : 'No',
                    date('Y-m-d H:i:s', $customer->created),
                ];
            }, $allCustomers);

            $this->table(
                ['ID', 'Name', 'Email', 'Currency', 'Balance', 'Delinquent', 'Live Mode', 'Created'],
                $rows
            );
        }

        if ($save) {
            $this->info('Saving customers to the stripe_customers table...');
            $created = 0;
            $updated = 0;
            $linked = 0;
            foreach ($allCustomers as $customer) {
                $data = [
                    'stripe_id' => $customer->id,
                    'address' => $customer->address,
                    'balance' => $customer->balance,
                    'created' => $customer->created,
                    'currency' => $customer->currency,
                    'default_source' => $customer->default_source,
                    'delinquent' => $customer->delinquent,
                    'description' => $customer->description,
                    'email' => $customer->email,
                    'invoice_prefix' => $customer->invoice_prefix,
                    'invoice_settings' => $customer->invoice_settings,
                    'livemode' => $customer->livemode,
                    'metadata' => $customer->metadata,
                    'name' => $customer->name,
                    'next_invoice_sequence' => $customer->next_invoice_sequence,
                    'phone' => $customer->phone,
                    'preferred_locales' => $customer->preferred_locales,
                    'shipping' => $customer->shipping,
                    'tax_exempt' => $customer->tax_exempt,
                    'test_clock' => $customer->test_clock,
                ];

                // Check if customer exists by email for one-to-one relationship
                $existingCustomer = null;
                $userId = null;
                if ($customer->email) {
                    $existingCustomer = \App\Models\StripeCustomer::where('email', $customer->email)->first();
                    if ($existingCustomer) {
                        $linked++;
                    }

                    // Find user by email for user_id relationship
                    $user = \App\Models\User::where('email', $customer->email)->first();
                    if ($user) {
                        $userId = $user->id;
                    }
                }

                // Add user_id to data if found
                if ($userId) {
                    $data['user_id'] = $userId;
                }

                if ($existingCustomer) {
                    // Update existing customer
                    $existingCustomer->update($data);
                    $updated++;
                } else {
                    // Create new customer
                    \App\Models\StripeCustomer::create($data);
                    $created++;
                }
            }
            $this->info("Saved customers: Created $created, Updated $updated");
            $this->info("Email-linked customers: $linked");
        }

        $this->info("Total customers fetched: " . count($allCustomers));
        $this->info('Done!');
        return 0;
    }
}
