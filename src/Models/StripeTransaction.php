<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_id',
        'object_type',
        'amount_value',
        'amount_currency',
        'balance_impact_available_value',
        'balance_impact_available_currency',
        'balance_impact_inbound_pending_value',
        'balance_impact_inbound_pending_currency',
        'balance_impact_outbound_pending_value',
        'balance_impact_outbound_pending_currency',
        'category',
        'financial_account',
        'status',
        'flow_type',
        'flow_outbound_transfer',
        'status_transitions_posted_at',
        'status_transitions_void_at',
        'stripe_created_at',
        // Charge-specific fields
        'charge_id',
        'payment_intent_id',
        'customer_id',
        'payment_method_id',
        'invoice_id',
        'balance_transaction_id',
        'amount_captured',
        'amount_refunded',
        'currency',
        'captured',
        'disputed',
        'refunded',
        'failure_code',
        'failure_message',
        'description',
        'metadata',
        'statement_descriptor',
        'statement_descriptor_suffix',
        'calculated_statement_descriptor',
        'receipt_email',
        'receipt_number',
        'receipt_url',
        'payment_method_type',
        'payment_method_details',
        'billing_details',
        'outcome',
        'fraud_details',
        'radar_options',
        'shipping',
        'transfer_group',
        'transfer_data',
        'application',
        'application_fee',
        'application_fee_amount',
        'destination',
        'on_behalf_of',
        'order',
        'source',
        'source_transfer',
        'dispute',
        'review',
        'failure_balance_transaction',
    ];

    protected $casts = [
        'amount_value' => 'integer',
        'balance_impact_available_value' => 'integer',
        'balance_impact_inbound_pending_value' => 'integer',
        'balance_impact_outbound_pending_value' => 'integer',
        'status_transitions_posted_at' => 'datetime',
        'status_transitions_void_at' => 'datetime',
        'stripe_created_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // Charge-specific casts
        'amount_captured' => 'integer',
        'amount_refunded' => 'integer',
        'captured' => 'boolean',
        'disputed' => 'boolean',
        'refunded' => 'boolean',
        'metadata' => 'array',
        'payment_method_details' => 'array',
        'billing_details' => 'array',
        'outcome' => 'array',
        'fraud_details' => 'array',
        'radar_options' => 'array',
        'shipping' => 'array',
        'transfer_data' => 'array',
        'application_fee_amount' => 'integer',
    ];

    /**
     * Get the formatted amount with currency
     */
    public function getFormattedAmountAttribute(): string
    {
        $amount = $this->amount_value / 100; // Convert from cents

        return number_format($amount, 2).' '.strtoupper($this->amount_currency);
    }

    /**
     * Get the amount as a decimal
     */
    public function getAmountDecimalAttribute(): float
    {
        return $this->amount_value / 100;
    }

    /**
     * Get the captured amount as a decimal
     */
    public function getAmountCapturedDecimalAttribute(): ?float
    {
        if ($this->amount_captured === null) {
            return null;
        }

        return $this->amount_captured / 100;
    }

    /**
     * Get the refunded amount as a decimal
     */
    public function getAmountRefundedDecimalAttribute(): ?float
    {
        if ($this->amount_refunded === null) {
            return null;
        }

        return $this->amount_refunded / 100;
    }

    /**
     * Get the available balance impact as a decimal
     */
    public function getAvailableBalanceImpactDecimalAttribute(): ?float
    {
        if ($this->balance_impact_available_value === null) {
            return null;
        }

        return $this->balance_impact_available_value / 100;
    }

    /**
     * Get the inbound pending balance impact as a decimal
     */
    public function getInboundPendingBalanceImpactDecimalAttribute(): ?float
    {
        if ($this->balance_impact_inbound_pending_value === null) {
            return null;
        }

        return $this->balance_impact_inbound_pending_value / 100;
    }

    /**
     * Get the outbound pending balance impact as a decimal
     */
    public function getOutboundPendingBalanceImpactDecimalAttribute(): ?float
    {
        if ($this->balance_impact_outbound_pending_value === null) {
            return null;
        }

        return $this->balance_impact_outbound_pending_value / 100;
    }

    /**
     * Check if this is an outbound transfer
     */
    public function isOutboundTransfer(): bool
    {
        return $this->category === 'outbound_transfer';
    }

    /**
     * Check if this is an inbound transfer
     */
    public function isInboundTransfer(): bool
    {
        return $this->category === 'inbound_transfer';
    }

    /**
     * Check if the transaction is posted
     */
    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }

    /**
     * Check if the transaction is voided
     */
    public function isVoided(): bool
    {
        return $this->status === 'void' || $this->status_transitions_void_at !== null;
    }

    /**
     * Check if this is a charge transaction
     */
    public function isCharge(): bool
    {
        return $this->charge_id !== null;
    }

    /**
     * Check if the charge was successful
     */
    public function isSuccessful(): bool
    {
        return $this->isCharge() && $this->status === 'succeeded';
    }

    /**
     * Check if the charge failed
     */
    public function isFailed(): bool
    {
        return $this->isCharge() && $this->failure_code !== null;
    }

    /**
     * Get the card brand from payment method details
     */
    public function getCardBrandAttribute(): ?string
    {
        if (! $this->payment_method_details || ! isset($this->payment_method_details['card'])) {
            return null;
        }

        return $this->payment_method_details['card']['brand'] ?? null;
    }

    /**
     * Get the last 4 digits from payment method details
     */
    public function getCardLast4Attribute(): ?string
    {
        if (! $this->payment_method_details || ! isset($this->payment_method_details['card'])) {
            return null;
        }

        return $this->payment_method_details['card']['last4'] ?? null;
    }

    /**
     * Get the authorization code from payment method details
     */
    public function getAuthorizationCodeAttribute(): ?string
    {
        if (! $this->payment_method_details || ! isset($this->payment_method_details['card'])) {
            return null;
        }

        return $this->payment_method_details['card']['authorization_code'] ?? null;
    }

    /**
     * Get the network transaction ID from payment method details
     */
    public function getNetworkTransactionIdAttribute(): ?string
    {
        if (! $this->payment_method_details || ! isset($this->payment_method_details['card'])) {
            return null;
        }

        return $this->payment_method_details['card']['network_transaction_id'] ?? null;
    }

    /**
     * Get the risk level from outcome
     */
    public function getRiskLevelAttribute(): ?string
    {
        if (! $this->outcome) {
            return null;
        }

        return $this->outcome['risk_level'] ?? null;
    }

    /**
     * Get the risk score from outcome
     */
    public function getRiskScoreAttribute(): ?int
    {
        if (! $this->outcome) {
            return null;
        }

        return $this->outcome['risk_score'] ?? null;
    }

    /**
     * Get the seller message from outcome
     */
    public function getSellerMessageAttribute(): ?string
    {
        if (! $this->outcome) {
            return null;
        }

        return $this->outcome['seller_message'] ?? null;
    }

    /**
     * Get the network status from outcome
     */
    public function getNetworkStatusAttribute(): ?string
    {
        if (! $this->outcome) {
            return null;
        }

        return $this->outcome['network_status'] ?? null;
    }

    /**
     * Get the flow object as an array
     */
    public function getFlowAttribute(): array
    {
        return [
            'type' => $this->flow_type,
            'outbound_transfer' => $this->flow_outbound_transfer,
        ];
    }

    /**
     * Get the balance impact object as an array
     */
    public function getBalanceImpactAttribute(): array
    {
        return [
            'available' => [
                'value' => $this->balance_impact_available_value,
                'currency' => $this->balance_impact_available_currency,
            ],
            'inbound_pending' => [
                'value' => $this->balance_impact_inbound_pending_value,
                'currency' => $this->balance_impact_inbound_pending_currency,
            ],
            'outbound_pending' => [
                'value' => $this->balance_impact_outbound_pending_value,
                'currency' => $this->balance_impact_outbound_pending_currency,
            ],
        ];
    }

    /**
     * Get the status transitions object as an array
     */
    public function getStatusTransitionsAttribute(): array
    {
        return [
            'posted_at' => $this->status_transitions_posted_at,
            'void_at' => $this->status_transitions_void_at,
        ];
    }

    /**
     * Scope to filter by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by financial account
     */
    public function scopeByFinancialAccount($query, string $financialAccount)
    {
        return $query->where('financial_account', $financialAccount);
    }

    /**
     * Scope to filter outbound transfers
     */
    public function scopeOutboundTransfers($query)
    {
        return $query->where('category', 'outbound_transfer');
    }

    /**
     * Scope to filter inbound transfers
     */
    public function scopeInboundTransfers($query)
    {
        return $query->where('category', 'inbound_transfer');
    }

    /**
     * Scope to filter posted transactions
     */
    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    /**
     * Scope to filter charges
     */
    public function scopeCharges($query)
    {
        return $query->whereNotNull('charge_id');
    }

    /**
     * Scope to filter successful charges
     */
    public function scopeSuccessfulCharges($query)
    {
        return $query->whereNotNull('charge_id')->where('status', 'succeeded');
    }

    /**
     * Scope to filter failed charges
     */
    public function scopeFailedCharges($query)
    {
        return $query->whereNotNull('charge_id')->whereNotNull('failure_code');
    }

    /**
     * Scope to filter by customer
     */
    public function scopeByCustomer($query, string $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope to filter by payment method
     */
    public function scopeByPaymentMethod($query, string $paymentMethodId)
    {
        return $query->where('payment_method_id', $paymentMethodId);
    }

    /**
     * Scope to filter by invoice
     */
    public function scopeByInvoice($query, string $invoiceId)
    {
        return $query->where('invoice_id', $invoiceId);
    }

    /**
     * Scope to filter by payment intent
     */
    public function scopeByPaymentIntent($query, string $paymentIntentId)
    {
        return $query->where('payment_intent_id', $paymentIntentId);
    }

    /**
     * Get the user associated with this transaction through customer_id
     */
    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'customer_id', 'stripe_id');
    }

    /**
     * Get the tenant associated with this transaction through the user
     */
    public function tenant()
    {
        $tenantModel = config('stripe-product-manager.models.tenant');

        // If no tenant model is configured, return a relationship that will always be empty
        if (! $tenantModel || ! class_exists($tenantModel)) {
            return $this->belongsTo(config('auth.providers.users.model'), 'id', 'id')
                ->whereRaw('1 = 0'); // This ensures no results are returned
        }

        return $this->hasOneThrough(
            $tenantModel,
            config('auth.providers.users.model'),
            'stripe_id', // Foreign key on users table
            'id', // Foreign key on tenants table
            'customer_id', // Local key on stripe_transactions table
            'tenant_id' // Local key on users table
        );
    }

    /**
     * Get the tenant name for display
     */
    public function getTenantNameAttribute(): ?string
    {
        $tenantModel = config('stripe-product-manager.models.tenant');

        // If no tenant model is configured, return null
        if (! $tenantModel || ! class_exists($tenantModel)) {
            return null;
        }

        return $this->tenant?->name;
    }

    /**
     * Get the number of attempts for this payment intent
     */
    public function getAttemptsCountAttribute(): int
    {
        if (! $this->payment_intent_id) {
            return 1; // If no payment intent, assume single attempt
        }

        return static::where('payment_intent_id', $this->payment_intent_id)->count();
    }
}
