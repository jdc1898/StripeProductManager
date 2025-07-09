<?php

namespace Fullstack\StripeProductManager\Console\Commands\Stripe;

use App\Models\User;
use Illuminate\Console\Command;
use Laravel\Cashier\Exceptions\IncompletePayment;

class GetMeterUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meter:usage
                            {user : The user ID or email to get usage for}
                            {--subscription=default : The subscription name to get usage for}
                            {--period=current : The billing period (current, previous, or specific date YYYY-MM)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get meter usage for a specific user and subscription';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userIdentifier = $this->argument('user');
        $subscriptionName = $this->option('subscription');
        $period = $this->option('period');

        // Find the user
        $user = $this->findUser($userIdentifier);
        if (!$user) {
            $this->error("User not found: {$userIdentifier}");
            return 1;
        }

        $this->info("Found user: {$user->name} ({$user->email})");

        // Get the subscription
        $subscription = $user->subscription($subscriptionName);
        if (!$subscription) {
            $this->error("No active subscription found for user with name: {$subscriptionName}");
            return 1;
        }

        $this->info("Found subscription: {$subscription->stripe_id}");

        // Get usage for the specified period
        try {
            $usage = $this->getUsageForPeriod($subscription, $period);
            $this->displayUsage($usage, $period);
        } catch (\Exception $e) {
            $this->error("Error getting usage: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Find user by ID or email
     */
    private function findUser($identifier): ?User
    {
        if (is_numeric($identifier)) {
            return User::find($identifier);
        }

        return User::where('email', $identifier)->first();
    }

    /**
     * Get usage for the specified period
     */
    private function getUsageForPeriod($subscription, $period)
    {
        switch ($period) {
            case 'current':
                return $subscription->usage();

            case 'previous':
                // Get previous billing period
                $currentPeriodStart = $subscription->asStripeSubscription()->current_period_start;
                $previousPeriodStart = $currentPeriodStart - (30 * 24 * 60 * 60); // 30 days before
                $previousPeriodEnd = $currentPeriodStart;

                return $subscription->usage([
                    'start' => $previousPeriodStart,
                    'end' => $previousPeriodEnd,
                ]);

            default:
                // Assume it's a date in YYYY-MM format
                if (preg_match('/^\d{4}-\d{2}$/', $period)) {
                    $startDate = \Carbon\Carbon::createFromFormat('Y-m', $period)->startOfMonth();
                    $endDate = $startDate->copy()->endOfMonth();

                    return $subscription->usage([
                        'start' => $startDate->timestamp,
                        'end' => $endDate->timestamp,
                    ]);
                }

                throw new \InvalidArgumentException("Invalid period format. Use 'current', 'previous', or 'YYYY-MM'");
        }
    }

    /**
     * Display usage information
     */
    private function displayUsage($usage, $period)
    {
        $this->info("\n📊 Usage Report for period: {$period}");
        $this->line(str_repeat('-', 50));

        if (empty($usage)) {
            $this->warn("No usage data found for this period.");
            return;
        }

        $headers = ['Subscription Item', 'Usage', 'Period Start', 'Period End'];
        $rows = [];

        foreach ($usage as $item) {
            $rows[] = [
                $item->subscription_item,
                number_format($item->total_usage),
                date('Y-m-d H:i:s', $item->period->start),
                date('Y-m-d H:i:s', $item->period->end),
            ];
        }

        $this->table($headers, $rows);

        // Show total usage
        $totalUsage = collect($usage)->sum('total_usage');
        $this->info("\n📈 Total Usage: " . number_format($totalUsage));
    }
}
