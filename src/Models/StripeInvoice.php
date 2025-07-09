<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Subscription;

class StripeInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_id',
        'object_type',
        'account_country',
        'account_name',
        'account_tax_ids',
        'amount_due',
        'amount_paid',
        'amount_overpaid',
        'amount_remaining',
        'amount_shipping',
        'currency',
        'subtotal',
        'subtotal_excluding_tax',
        'total',
        'total_excluding_tax',
        'starting_balance',
        'ending_balance',
        'customer',
        'customer_email',
        'customer_name',
        'customer_phone',
        'customer_tax_exempt',
        'customer_tax_ids',
        'customer_address',
        'customer_shipping',
        'billing_reason',
        'collection_method',
        'status',
        'number',
        'description',
        'footer',
        'receipt_number',
        'statement_descriptor',
        'payment_intent',
        'default_payment_method',
        'default_source',
        'paid',
        'paid_out_of_band',
        'attempted',
        'attempt_count',
        'auto_advance',
        'period_start',
        'period_end',
        'due_date',
        'next_payment_attempt',
        'automatic_tax',
        'default_tax_rates',
        'total_taxes',
        'total_discount_amounts',
        'pre_payment_credit_notes_amount',
        'post_payment_credit_notes_amount',
        'shipping_cost',
        'shipping_details',
        'hosted_invoice_url',
        'invoice_pdf',
        'confirmation_secret',
        'issuer',
        'parent',
        'from_invoice',
        'latest_revision',
        'payment_settings',
        'transfer_data',
        'test_clock',
        'status_transitions_finalized_at',
        'status_transitions_marked_uncollectible_at',
        'status_transitions_paid_at',
        'status_transitions_voided_at',
        'custom_fields',
        'discounts',
        'metadata',
        'application',
        'on_behalf_of',
        'last_finalization_error',
        'stripe_created_at',
        'webhooks_delivered_at',
        'livemode',
    ];

    protected $casts = [
        'amount_due' => 'integer',
        'amount_paid' => 'integer',
        'amount_overpaid' => 'integer',
        'amount_remaining' => 'integer',
        'amount_shipping' => 'integer',
        'subtotal' => 'integer',
        'subtotal_excluding_tax' => 'integer',
        'total' => 'integer',
        'total_excluding_tax' => 'integer',
        'starting_balance' => 'integer',
        'ending_balance' => 'integer',
        'pre_payment_credit_notes_amount' => 'integer',
        'post_payment_credit_notes_amount' => 'integer',
        'attempt_count' => 'integer',
        'paid' => 'boolean',
        'paid_out_of_band' => 'boolean',
        'attempted' => 'boolean',
        'auto_advance' => 'boolean',
        'livemode' => 'boolean',
        'account_tax_ids' => 'array',
        'customer_tax_ids' => 'array',
        'customer_address' => 'array',
        'customer_shipping' => 'array',
        'automatic_tax' => 'array',
        'default_tax_rates' => 'array',
        'total_taxes' => 'array',
        'total_discount_amounts' => 'array',
        'shipping_cost' => 'array',
        'shipping_details' => 'array',
        'issuer' => 'array',
        'payment_settings' => 'array',
        'transfer_data' => 'array',
        'custom_fields' => 'array',
        'discounts' => 'array',
        'metadata' => 'array',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'due_date' => 'datetime',
        'next_payment_attempt' => 'datetime',
        'status_transitions_finalized_at' => 'datetime',
        'status_transitions_marked_uncollectible_at' => 'datetime',
        'status_transitions_paid_at' => 'datetime',
        'status_transitions_voided_at' => 'datetime',
        'stripe_created_at' => 'datetime',
        'webhooks_delivered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the formatted amount due with currency
     */
    public function getFormattedAmountDueAttribute(): string
    {
        $amount = $this->amount_due / 100; // Convert from cents

        return number_format($amount, 2).' '.strtoupper($this->currency);
    }

    /**
     * Get the formatted amount paid with currency
     */
    public function getFormattedAmountPaidAttribute(): string
    {
        $amount = $this->amount_paid / 100; // Convert from cents

        return number_format($amount, 2).' '.strtoupper($this->currency);
    }

    /**
     * Get the formatted total with currency
     */
    public function getFormattedTotalAttribute(): string
    {
        $amount = $this->total / 100; // Convert from cents

        return number_format($amount, 2).' '.strtoupper($this->currency);
    }

    /**
     * Get the amount due as a decimal
     */
    public function getAmountDueDecimalAttribute(): float
    {
        return $this->amount_due / 100;
    }

    /**
     * Get the amount paid as a decimal
     */
    public function getAmountPaidDecimalAttribute(): float
    {
        return $this->amount_paid / 100;
    }

    /**
     * Get the total as a decimal
     */
    public function getTotalDecimalAttribute(): float
    {
        return $this->total / 100;
    }

    /**
     * Get the amount remaining as a decimal
     */
    public function getAmountRemainingDecimalAttribute(): float
    {
        return $this->amount_remaining / 100;
    }

    /**
     * Check if invoice is draft
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if invoice is open
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if invoice is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if invoice is void
     */
    public function isVoid(): bool
    {
        return $this->status === 'void';
    }

    /**
     * Check if invoice is uncollectible
     */
    public function isUncollectible(): bool
    {
        return $this->status === 'uncollectible';
    }

    /**
     * Check if invoice is finalized
     */
    public function isFinalized(): bool
    {
        return $this->status_transitions_finalized_at !== null;
    }

    /**
     * Check if invoice is voided
     */
    public function isVoided(): bool
    {
        return $this->status_transitions_voided_at !== null;
    }

    /**
     * Check if invoice is marked as uncollectible
     */
    public function isMarkedUncollectible(): bool
    {
        return $this->status_transitions_marked_uncollectible_at !== null;
    }

    /**
     * Check if this is a manual invoice
     */
    public function isManual(): bool
    {
        return $this->billing_reason === 'manual';
    }

    /**
     * Check if this is a subscription invoice
     */
    public function isSubscription(): bool
    {
        return $this->billing_reason === 'subscription';
    }

    /**
     * Check if this is a subscription cycle invoice
     */
    public function isSubscriptionCycle(): bool
    {
        return $this->billing_reason === 'subscription_cycle';
    }

    /**
     * Check if this is a subscription update invoice
     */
    public function isSubscriptionUpdate(): bool
    {
        return $this->billing_reason === 'subscription_update';
    }

    /**
     * Check if this is a subscription threshold invoice
     */
    public function isSubscriptionThreshold(): bool
    {
        return $this->billing_reason === 'subscription_threshold';
    }

    /**
     * Check if collection method is charge automatically
     */
    public function isChargeAutomatically(): bool
    {
        return $this->collection_method === 'charge_automatically';
    }

    /**
     * Check if collection method is send invoice
     */
    public function isSendInvoice(): bool
    {
        return $this->collection_method === 'send_invoice';
    }

    /**
     * Get the status transitions object as an array
     */
    public function getStatusTransitionsAttribute(): array
    {
        return [
            'finalized_at' => $this->status_transitions_finalized_at,
            'marked_uncollectible_at' => $this->status_transitions_marked_uncollectible_at,
            'paid_at' => $this->status_transitions_paid_at,
            'voided_at' => $this->status_transitions_voided_at,
        ];
    }

    /**
     * Get the customer object as an array
     */
    public function getCustomerObjectAttribute(): array
    {
        return [
            'id' => $this->customer,
            'email' => $this->customer_email,
            'name' => $this->customer_name,
            'phone' => $this->customer_phone,
            'tax_exempt' => $this->customer_tax_exempt,
            'tax_ids' => $this->customer_tax_ids,
            'address' => $this->customer_address,
            'shipping' => $this->customer_shipping,
        ];
    }

    /**
     * Get the account object as an array
     */
    public function getAccountObjectAttribute(): array
    {
        return [
            'country' => $this->account_country,
            'name' => $this->account_name,
            'tax_ids' => $this->account_tax_ids,
        ];
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by billing reason
     */
    public function scopeByBillingReason($query, string $billingReason)
    {
        return $query->where('billing_reason', $billingReason);
    }

    /**
     * Scope to filter by collection method
     */
    public function scopeByCollectionMethod($query, string $collectionMethod)
    {
        return $query->where('collection_method', $collectionMethod);
    }

    /**
     * Scope to filter by customer
     */
    public function scopeByCustomer($query, string $customerId)
    {
        return $query->where('customer', $customerId);
    }

    /**
     * Scope to filter paid invoices
     */
    public function scopePaid($query)
    {
        return $query->where('paid', true);
    }

    /**
     * Scope to filter unpaid invoices
     */
    public function scopeUnpaid($query)
    {
        return $query->where('paid', false);
    }

    /**
     * Scope to filter draft invoices
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to filter open invoices
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope to filter void invoices
     */
    public function scopeVoid($query)
    {
        return $query->where('status', 'void');
    }

    /**
     * Scope to filter manual invoices
     */
    public function scopeManual($query)
    {
        return $query->where('billing_reason', 'manual');
    }

    /**
     * Scope to filter subscription invoices
     */
    public function scopeSubscription($query)
    {
        return $query->where('billing_reason', 'subscription');
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('stripe_created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by amount range
     */
    public function scopeByAmountRange($query, $minAmount, $maxAmount)
    {
        return $query->whereBetween('total', [$minAmount, $maxAmount]);
    }

    /**
     * Get the user relationship
     */
    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'customer', 'stripe_id');
    }

    /**
     * Get the subscription relationship (if this is a subscription invoice)
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription', 'stripe_id');
    }
}
