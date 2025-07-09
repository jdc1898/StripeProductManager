<?php

namespace App\Http\Controllers;

use App\Models\StripeInvoice;
use App\Models\StripeTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Subscription;
use Stripe\Event;

class StripeWebhookController extends Controller
{
    /**
     * Handle incoming Stripe webhook events
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $event = null;

        try {
            $event = Event::constructFrom(json_decode($payload, true));
        } catch (\UnexpectedValueException $e) {
            Log::error('Stripe webhook: Invalid payload', ['error' => $e->getMessage()]);

            return response('Invalid payload', 400);
        }

        // Log minimal event info for debugging
        Log::info('Stripe webhook received', [
            'type' => $event->type,
            'id' => $event->id,
        ]);

        // Handle the event
        switch ($event->type) {
            case 'charge.succeeded':
                $this->handleChargeSucceeded($event->data->object);

                break;
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);

                break;
            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($event->data->object);

                break;
            case 'invoice.payment_failed':
                $this->handleInvoicePaymentFailed($event->data->object);

                break;
            case 'invoice.created':
                $this->handleInvoiceCreated($event->data->object);

                break;
            case 'invoice.updated':
                $this->handleInvoiceUpdated($event->data->object);

                break;
            case 'invoice.finalized':
                $this->handleInvoiceFinalized($event->data->object);

                break;
            case 'invoice.paid':
                $this->handleInvoicePaid($event->data->object);

                break;
            case 'invoice.voided':
                $this->handleInvoiceVoided($event->data->object);

                break;
            case 'customer.subscription.created':
                $this->handleSubscriptionCreated($event->data->object);

                break;
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);

                break;
            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);

                break;
            case 'v2.money_management.transaction.created':
                $this->handleMoneyManagementTransactionCreated($event->data->object);

                break;
            case 'v2.money_management.transaction.updated':
                $this->handleMoneyManagementTransactionUpdated($event->data->object);

                break;
            default:
                Log::info('Unhandled Stripe webhook event', ['type' => $event->type]);
        }

        return response('Webhook handled', 200);
    }

    /**
     * Handle successful charge events
     */
    protected function handleChargeSucceeded($charge)
    {

        // Create or update StripeTransaction record with all the rich charge data
        StripeTransaction::updateOrCreate(
            ['stripe_id' => $charge->id],
            [
                'object_type' => 'charge',
                'amount_value' => $charge->amount,
                'amount_currency' => $charge->currency ?? 'usd',
                'category' => 'charge',
                'status' => $charge->status,
                'flow_type' => 'charge',
                'flow_outbound_transfer' => null,
                'stripe_created_at' => $charge->created ? \Carbon\Carbon::createFromTimestamp($charge->created) : null,

                // Charge-specific fields
                'charge_id' => $charge->id,
                'payment_intent_id' => $charge->payment_intent ?? null,
                'customer_id' => $charge->customer ?? null,
                'payment_method_id' => $charge->payment_method ?? null,
                'invoice_id' => $charge->invoice ?? null,
                'balance_transaction_id' => $charge->balance_transaction ?? null,

                // Amount fields
                'amount_captured' => $charge->amount_captured ?? null,
                'amount_refunded' => $charge->amount_refunded ?? null,
                'currency' => $charge->currency,

                // Status and flags
                'captured' => $charge->captured ?? false,
                'disputed' => $charge->disputed ?? false,
                'refunded' => $charge->refunded ?? false,
                'failure_code' => $charge->failure_code ?? null,
                'failure_message' => $charge->failure_message ?? null,

                // Description and metadata
                'description' => $charge->description ?? null,
                'metadata' => $charge->metadata ? $charge->metadata->toArray() : null,
                'statement_descriptor' => $charge->statement_descriptor ?? null,
                'statement_descriptor_suffix' => $charge->statement_descriptor_suffix ?? null,
                'calculated_statement_descriptor' => $charge->calculated_statement_descriptor ?? null,

                // Receipt information
                'receipt_email' => $charge->receipt_email ?? null,
                'receipt_number' => $charge->receipt_number ?? null,
                'receipt_url' => $charge->receipt_url ?? null,

                // Payment method details
                'payment_method_type' => $charge->payment_method_details->type ?? null,
                'payment_method_details' => $charge->payment_method_details ? $charge->payment_method_details->toArray() : null,

                // Billing details
                'billing_details' => $charge->billing_details ? $charge->billing_details->toArray() : null,

                // Outcome information
                'outcome' => $charge->outcome ? $charge->outcome->toArray() : null,

                // Fraud and risk details
                'fraud_details' => $charge->fraud_details ? $charge->fraud_details->toArray() : null,
                'radar_options' => $charge->radar_options ? $charge->radar_options->toArray() : null,

                // Shipping information
                'shipping' => $charge->shipping ? $charge->shipping->toArray() : null,

                // Transfer information
                'transfer_group' => $charge->transfer_group ?? null,
                'transfer_data' => $charge->transfer_data ? $charge->transfer_data->toArray() : null,

                // Application and fees
                'application' => $charge->application ?? null,
                'application_fee' => $charge->application_fee ?? null,
                'application_fee_amount' => $charge->application_fee_amount ?? null,

                // Destination and on_behalf_of
                'destination' => $charge->destination ?? null,
                'on_behalf_of' => $charge->on_behalf_of ?? null,

                // Order and source
                'order' => $charge->order ?? null,
                'source' => $charge->source ?? null,
                'source_transfer' => $charge->source_transfer ?? null,

                // Review and dispute
                'dispute' => $charge->dispute ?? null,
                'review' => $charge->review ?? null,

                // Failure balance transaction
                'failure_balance_transaction' => $charge->failure_balance_transaction ?? null,
            ]
        );

        // Also keep the old Transaction model for backward compatibility
        StripeTransaction::updateOrCreate(
            ['charge_id' => $charge->id],
            [
                'event_id' => $charge->id,
                'transaction_id' => $charge->balance_transaction ?? null,
                'invoice_id' => $charge->invoice ?? null,
                'customer_id' => $charge->customer ?? null,
                'payment_method_id' => $charge->payment_method ?? null,
                'amount' => $charge->amount,
                'transaction_date' => \Carbon\Carbon::createFromTimestamp($charge->created),
                'paid' => $charge->paid,
                'payment_method_type' => $charge->payment_method_details->type ?? null,
                'payment_method_details_card_brand' => $charge->payment_method_details->card->brand ?? null,
                'payment_method_details_card_last4' => $charge->payment_method_details->card->last4 ?? null,
                'payment_method_details_card_exp_month' => $charge->payment_method_details->card->exp_month ?? null,
                'payment_method_details_card_exp_year' => $charge->payment_method_details->card->exp_year ?? null,
                'payment_method_details_authorization_code' => $charge->payment_method_details->card->authorization_code ?? null,
                'receipt_url' => $charge->receipt_url ?? null,
                'status' => $charge->status,
            ]
        );
    }

    /**
     * Handle successful payment intent events
     */
    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        // If there's a charge associated with this payment intent, it will be handled by charge.succeeded
        // This is mainly for logging and any payment intent specific logic
    }

    /**
     * Handle successful invoice payment events
     */
    protected function handleInvoicePaymentSucceeded($invoice)
    {
        // Update the StripeInvoice record
        $this->createOrUpdateStripeInvoice($invoice);
    }

    /**
     * Handle failed invoice payment events
     */
    protected function handleInvoicePaymentFailed($invoice)
    {
        // Update the StripeInvoice record
        $this->createOrUpdateStripeInvoice($invoice);
    }

    /**
     * Handle subscription created events
     */
    protected function handleSubscriptionCreated($subscription)
    {
        // This could be used to create local subscription records if needed
        // Currently handled by Cashier's built-in webhook handling
    }

    /**
     * Handle subscription updated events
     */
    protected function handleSubscriptionUpdated($subscription)
    {
        Log::info('Processing customer.subscription.updated', ['subscription_id' => $subscription->id]);

        // Update local subscription record
        Subscription::where('stripe_id', $subscription->id)->update([
            'stripe_status' => $subscription->status,
            'quantity' => $subscription->quantity,
            'trial_ends_at' => $subscription->trial_end ? \Carbon\Carbon::createFromTimestamp($subscription->trial_end) : null,
            'ends_at' => $subscription->ended_at ? \Carbon\Carbon::createFromTimestamp($subscription->ended_at) : null,
        ]);
    }

    /**
     * Handle subscription deleted events
     */
    protected function handleSubscriptionDeleted($subscription)
    {
        // Update local subscription record
        Subscription::where('stripe_id', $subscription->id)->update([
            'stripe_status' => $subscription->status,
            'ends_at' => $subscription->ended_at ? \Carbon\Carbon::createFromTimestamp($subscription->ended_at) : now(),
        ]);
    }

    /**
     * Get user ID from Stripe customer ID
     */
    protected function getUserIdFromCustomer($customerId)
    {
        if (! $customerId) {
            return null;
        }

        $user = User::where('stripe_id', $customerId)->first();

        return $user ? $user->id : null;
    }

    /**
     * Handle invoice created events
     */
    protected function handleInvoiceCreated($invoice)
    {
        $this->createOrUpdateStripeInvoice($invoice);
    }

    /**
     * Handle invoice updated events
     */
    protected function handleInvoiceUpdated($invoice)
    {
        $this->createOrUpdateStripeInvoice($invoice);
    }

    /**
     * Handle invoice finalized events
     */
    protected function handleInvoiceFinalized($invoice)
    {
        $this->createOrUpdateStripeInvoice($invoice);
    }

    /**
     * Handle invoice paid events
     */
    protected function handleInvoicePaid($invoice)
    {
        $this->createOrUpdateStripeInvoice($invoice);
    }

    /**
     * Handle invoice voided events
     */
    protected function handleInvoiceVoided($invoice)
    {
        Log::info('Processing invoice.voided', ['invoice_id' => $invoice->id]);
        $this->createOrUpdateStripeInvoice($invoice);
    }

    /**
     * Handle money management transaction created events
     */
    protected function handleMoneyManagementTransactionCreated($transaction)
    {
        Log::info('Processing v2.money_management.transaction.created', ['transaction_id' => $transaction->id]);
        $this->createOrUpdateStripeTransaction($transaction);
    }

    /**
     * Handle money management transaction updated events
     */
    protected function handleMoneyManagementTransactionUpdated($transaction)
    {
        Log::info('Processing v2.money_management.transaction.updated', ['transaction_id' => $transaction->id]);
        $this->createOrUpdateStripeTransaction($transaction);
    }

    /**
     * Create or update Stripe invoice record
     */
    protected function createOrUpdateStripeInvoice($invoice)
    {
        StripeInvoice::updateOrCreate(
            ['stripe_id' => $invoice->id],
            [
                'object_type' => $invoice->object ?? 'invoice',
                'account_country' => $invoice->account_country ?? null,
                'account_name' => $invoice->account_name ?? null,
                'account_tax_ids' => $invoice->account_tax_ids ?? null,
                'amount_due' => $invoice->amount_due ?? 0,
                'amount_paid' => $invoice->amount_paid ?? 0,
                'amount_overpaid' => $invoice->amount_overpaid ?? 0,
                'amount_remaining' => $invoice->amount_remaining ?? 0,
                'amount_shipping' => $invoice->amount_shipping ?? 0,
                'currency' => $invoice->currency ?? 'usd',
                'subtotal' => $invoice->subtotal ?? 0,
                'subtotal_excluding_tax' => $invoice->subtotal_excluding_tax ?? 0,
                'total' => $invoice->total ?? 0,
                'total_excluding_tax' => $invoice->total_excluding_tax ?? 0,
                'starting_balance' => $invoice->starting_balance ?? 0,
                'ending_balance' => $invoice->ending_balance ?? null,
                'customer' => $invoice->customer ?? null,
                'customer_email' => $invoice->customer_email ?? null,
                'customer_name' => $invoice->customer_name ?? null,
                'customer_phone' => $invoice->customer_phone ?? null,
                'customer_tax_exempt' => $invoice->customer_tax_exempt ?? null,
                'customer_tax_ids' => $invoice->customer_tax_ids ?? null,
                'customer_address' => $invoice->customer_address ?? null,
                'customer_shipping' => $invoice->customer_shipping ?? null,
                'billing_reason' => $invoice->billing_reason ?? null,
                'collection_method' => $invoice->collection_method ?? null,
                'status' => $invoice->status ?? null,
                'number' => $invoice->number ?? null,
                'description' => $invoice->description ?? null,
                'footer' => $invoice->footer ?? null,
                'receipt_number' => $invoice->receipt_number ?? null,
                'statement_descriptor' => $invoice->statement_descriptor ?? null,
                'payment_intent' => $invoice->payment_intent ?? null,
                'default_payment_method' => $invoice->default_payment_method ?? null,
                'default_source' => $invoice->default_source ?? null,
                'paid' => $invoice->paid ?? false,
                'paid_out_of_band' => $invoice->paid_out_of_band ?? false,
                'attempted' => $invoice->attempted ?? false,
                'attempt_count' => $invoice->attempt_count ?? 0,
                'auto_advance' => $invoice->auto_advance ?? false,
                'period_start' => $invoice->period_start ? \Carbon\Carbon::createFromTimestamp($invoice->period_start) : null,
                'period_end' => $invoice->period_end ? \Carbon\Carbon::createFromTimestamp($invoice->period_end) : null,
                'due_date' => $invoice->due_date ? \Carbon\Carbon::createFromTimestamp($invoice->due_date) : null,
                'next_payment_attempt' => $invoice->next_payment_attempt ? \Carbon\Carbon::createFromTimestamp($invoice->next_payment_attempt) : null,
                'automatic_tax' => $invoice->automatic_tax ?? null,
                'default_tax_rates' => $invoice->default_tax_rates ?? null,
                'total_taxes' => $invoice->total_taxes ?? null,
                'total_discount_amounts' => $invoice->total_discount_amounts ?? null,
                'pre_payment_credit_notes_amount' => $invoice->pre_payment_credit_notes_amount ?? 0,
                'post_payment_credit_notes_amount' => $invoice->post_payment_credit_notes_amount ?? 0,
                'shipping_cost' => $invoice->shipping_cost ?? null,
                'shipping_details' => $invoice->shipping_details ?? null,
                'hosted_invoice_url' => $invoice->hosted_invoice_url ?? null,
                'invoice_pdf' => $invoice->invoice_pdf ?? null,
                'confirmation_secret' => $invoice->confirmation_secret ?? null,
                'issuer' => $invoice->issuer ?? null,
                'parent' => $invoice->parent ?? null,
                'from_invoice' => $invoice->from_invoice ?? null,
                'latest_revision' => $invoice->latest_revision ?? null,
                'payment_settings' => $invoice->payment_settings ?? null,
                'transfer_data' => $invoice->transfer_data ?? null,
                'test_clock' => $invoice->test_clock ?? null,
                'status_transitions_finalized_at' => $invoice->status_transitions->finalized_at ? \Carbon\Carbon::createFromTimestamp($invoice->status_transitions->finalized_at) : null,
                'status_transitions_marked_uncollectible_at' => $invoice->status_transitions->marked_uncollectible_at ? \Carbon\Carbon::createFromTimestamp($invoice->status_transitions->marked_uncollectible_at) : null,
                'status_transitions_paid_at' => $invoice->status_transitions->paid_at ? \Carbon\Carbon::createFromTimestamp($invoice->status_transitions->paid_at) : null,
                'status_transitions_voided_at' => $invoice->status_transitions->voided_at ? \Carbon\Carbon::createFromTimestamp($invoice->status_transitions->voided_at) : null,
                'custom_fields' => $invoice->custom_fields ?? null,
                'discounts' => $invoice->discounts ?? null,
                'metadata' => $invoice->metadata ?? null,
                'application' => $invoice->application ?? null,
                'on_behalf_of' => $invoice->on_behalf_of ?? null,
                'last_finalization_error' => $invoice->last_finalization_error ?? null,
                'stripe_created_at' => $invoice->created ? \Carbon\Carbon::createFromTimestamp($invoice->created) : null,
                'webhooks_delivered_at' => $invoice->webhooks_delivered_at ? \Carbon\Carbon::createFromTimestamp($invoice->webhooks_delivered_at) : null,
                'livemode' => $invoice->livemode ?? false,
            ]
        );
    }

    /**
     * Create or update Stripe transaction record
     */
    protected function createOrUpdateStripeTransaction($transaction)
    {
        StripeTransaction::updateOrCreate(
            ['stripe_id' => $transaction->id],
            [
                'object_type' => $transaction->object ?? 'v2.money_management.transaction',
                'amount_value' => $transaction->amount->value ?? 0,
                'amount_currency' => $transaction->amount->currency ?? 'usd',
                'balance_impact_available_value' => $transaction->balance_impact->available->value ?? null,
                'balance_impact_available_currency' => $transaction->balance_impact->available->currency ?? null,
                'balance_impact_inbound_pending_value' => $transaction->balance_impact->inbound_pending->value ?? null,
                'balance_impact_inbound_pending_currency' => $transaction->balance_impact->inbound_pending->currency ?? null,
                'balance_impact_outbound_pending_value' => $transaction->balance_impact->outbound_pending->value ?? null,
                'balance_impact_outbound_pending_currency' => $transaction->balance_impact->outbound_pending->currency ?? null,
                'category' => $transaction->category ?? null,
                'financial_account' => $transaction->financial_account ?? null,
                'status' => $transaction->status ?? null,
                'flow_type' => $transaction->flow->type ?? null,
                'flow_outbound_transfer' => $transaction->flow->outbound_transfer ?? null,
                'status_transitions_posted_at' => $transaction->status_transitions->posted_at ? \Carbon\Carbon::parse($transaction->status_transitions->posted_at) : null,
                'status_transitions_void_at' => $transaction->status_transitions->void_at ? \Carbon\Carbon::parse($transaction->status_transitions->void_at) : null,
                'stripe_created_at' => $transaction->created ? \Carbon\Carbon::parse($transaction->created) : null,
            ]
        );
    }
}
