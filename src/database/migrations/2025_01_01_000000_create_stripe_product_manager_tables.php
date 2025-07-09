<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create stripe_products table
        Schema::create('stripe_products', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_id')->unique(); // Stripe product ID (e.g., prod_NWjs8kKbJWmuuc)
            $table->boolean('active')->default(true);
            $table->bigInteger('created')->nullable();
            $table->string('default_price')->nullable();
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->json('marketing_features')->nullable();
            $table->boolean('livemode')->default(false);
            $table->json('metadata')->nullable();
            $table->string('name');
            $table->json('package_dimensions')->nullable();
            $table->boolean('shippable')->nullable();
            $table->string('statement_descriptor', 22)->nullable();
            $table->string('tax_code')->nullable();
            $table->string('unit_label')->nullable();
            $table->bigInteger('updated')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();
        });

        // Create stripe_prices table
        Schema::create('stripe_prices', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_id')->unique(); // Stripe price ID (e.g., price_1MoBy5LkdIwHu7ixZhnattbh)
            $table->boolean('active')->default(true);
            $table->string('billing_scheme')->nullable();
            $table->bigInteger('created')->nullable();
            $table->string('currency', 3);
            $table->json('custom_unit_amount')->nullable();
            $table->boolean('livemode')->default(false);
            $table->string('lookup_key')->nullable();
            $table->json('metadata')->nullable();
            $table->string('nickname')->nullable();
            $table->string('product'); // Stripe product ID
            $table->json('recurring')->nullable();
            $table->string('tax_behavior')->nullable();
            $table->string('tiers_mode')->nullable();
            $table->json('tiers')->nullable();
            $table->json('transform_quantity')->nullable();
            $table->string('type');
            $table->integer('unit_amount')->nullable();
            $table->string('unit_amount_decimal')->nullable();
            $table->timestamps();
        });

        // Create stripe_meters table
        Schema::create('stripe_meters', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_id')->unique(); // Stripe meter ID (e.g., mtr_123)
            $table->bigInteger('created')->nullable();
            $table->json('customer_mapping')->nullable();
            $table->json('default_aggregation')->nullable();
            $table->string('display_name');
            $table->string('event_name');
            $table->json('event_time_window')->nullable();
            $table->boolean('livemode')->default(false);
            $table->string('status')->default('active');
            $table->json('status_transitions')->nullable();
            $table->bigInteger('updated')->nullable();
            $table->json('value_settings')->nullable();
            $table->timestamps();
        });

        // Create stripe_customers table
        Schema::create('stripe_customers', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_id')->unique(); // Stripe customer ID (e.g., cus_NffrFeUfNV2Hib)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('address')->nullable();
            $table->integer('balance')->default(0);
            $table->bigInteger('created')->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('default_source')->nullable();
            $table->boolean('delinquent')->default(false);
            $table->text('description')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('invoice_prefix')->nullable();
            $table->json('invoice_settings')->nullable();
            $table->boolean('livemode')->default(false);
            $table->json('metadata')->nullable();
            $table->string('name')->nullable();
            $table->integer('next_invoice_sequence')->default(1);
            $table->string('phone')->nullable();
            $table->json('preferred_locales')->nullable();
            $table->json('shipping')->nullable();
            $table->string('tax_exempt')->default('none');
            $table->string('test_clock')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'stripe_id']);
            $table->index('email');
        });

        // Create stripe_discounts table
        Schema::create('stripe_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_id')->unique();
            $table->string('object')->default('discount');
            $table->string('checkout_session')->nullable();
            $table->json('coupon')->nullable();
            $table->string('customer')->nullable();
            $table->timestamp('end')->nullable();
            $table->string('invoice')->nullable();
            $table->string('invoice_item')->nullable();
            $table->string('promotion_code')->nullable();
            $table->timestamp('start')->nullable();
            $table->string('subscription')->nullable();
            $table->timestamps();

            $table->index('customer');
            $table->index('subscription');
            $table->index('checkout_session');
        });

        // Create stripe_promotion_codes table
        Schema::create('stripe_promotion_codes', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_id')->unique();
            $table->string('object')->default('promotion_code');
            $table->boolean('active')->default(true);
            $table->string('code')->unique();
            $table->json('coupon')->nullable();
            $table->integer('created')->nullable();
            $table->string('customer')->nullable();
            $table->integer('expires_at')->nullable();
            $table->boolean('livemode')->default(false);
            $table->integer('max_redemptions')->nullable();
            $table->json('metadata')->nullable();
            $table->json('restrictions')->nullable();
            $table->integer('times_redeemed')->default(0);
            $table->timestamps();

            $table->index('customer');
            $table->index('active');
            $table->index('code');
        });

        // Create stripe_coupons table
        Schema::create('stripe_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_id')->unique();
            $table->string('object')->default('coupon');
            $table->integer('amount_off')->nullable();
            $table->integer('created')->nullable();
            $table->string('currency')->nullable();
            $table->string('duration')->default('once');
            $table->integer('duration_in_months')->nullable();
            $table->boolean('livemode')->default(false);
            $table->integer('max_redemptions')->nullable();
            $table->json('metadata')->nullable();
            $table->string('name')->nullable();
            $table->integer('percent_off')->nullable();
            $table->integer('redeem_by')->nullable();
            $table->integer('times_redeemed')->default(0);
            $table->boolean('valid')->default(true);
            $table->timestamps();

            $table->index('valid');
            $table->index('duration');
            $table->index('livemode');
        });

        // Create stripe_tax_codes table
        Schema::create('stripe_tax_codes', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_id')->unique();
            $table->string('object')->default('tax_code');
            $table->text('description')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();

            $table->index('name');
        });

        // Create stripe_tax_rates table
        Schema::create('stripe_tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_id')->unique();
            $table->string('object')->default('tax_rate');
            $table->boolean('active')->default(true);
            $table->string('country')->nullable();
            $table->integer('created')->nullable();
            $table->text('description')->nullable();
            $table->string('display_name')->nullable();
            $table->boolean('inclusive')->default(false);
            $table->string('jurisdiction')->nullable();
            $table->boolean('livemode')->default(false);
            $table->json('metadata')->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->string('state')->nullable();
            $table->string('tax_type')->nullable();
            $table->timestamps();

            $table->index('active');
            $table->index('country');
            $table->index('jurisdiction');
            $table->index('inclusive');
            $table->index('livemode');
        });

        // Create stripe_transactions table
        Schema::create('stripe_transactions', function (Blueprint $table) {
            $table->id();

            // Core Stripe transaction fields
            $table->string('stripe_id')->unique()->comment('Stripe transaction ID');
            $table->string('object_type')->comment('Stripe object type (e.g., v2.money_management.transaction)');

            // Amount information
            $table->bigInteger('amount_value')->comment('Transaction amount in smallest currency unit');
            $table->string('amount_currency', 3)->default('usd')->comment('Currency code');

            // Balance impact
            $table->bigInteger('balance_impact_available_value')->nullable()->comment('Available balance impact');
            $table->string('balance_impact_available_currency', 3)->nullable()->comment('Available balance currency');
            $table->bigInteger('balance_impact_inbound_pending_value')->nullable()->comment('Inbound pending balance impact');
            $table->string('balance_impact_inbound_pending_currency', 3)->nullable()->comment('Inbound pending currency');
            $table->bigInteger('balance_impact_outbound_pending_value')->nullable()->comment('Outbound pending balance impact');
            $table->string('balance_impact_outbound_pending_currency', 3)->nullable()->comment('Outbound pending currency');

            // Transaction details
            $table->string('category')->nullable()->comment('Transaction category (e.g., outbound_transfer)');
            $table->string('financial_account')->nullable()->comment('Financial account ID');
            $table->string('status')->nullable()->comment('Transaction status (e.g., posted)');

            // Flow information
            $table->string('flow_type')->nullable()->comment('Flow type (e.g., outbound_transfer)');
            $table->string('flow_outbound_transfer')->nullable()->comment('Outbound transfer ID');

            // Status transitions
            $table->timestamp('status_transitions_posted_at')->nullable()->comment('When transaction was posted');
            $table->timestamp('status_transitions_void_at')->nullable()->comment('When transaction was voided');

            // Charge-specific fields
            $table->string('charge_id')->nullable()->index();
            $table->string('payment_intent_id')->nullable()->index();
            $table->string('customer_id')->nullable()->index();
            $table->string('payment_method_id')->nullable()->index();
            $table->string('invoice_id')->nullable()->index();
            $table->string('balance_transaction_id')->nullable()->index();

            // Amount fields
            $table->integer('amount_captured')->nullable();
            $table->integer('amount_refunded')->nullable();
            $table->string('currency')->nullable();

            // Status and flags
            $table->boolean('captured')->default(false);
            $table->boolean('disputed')->default(false);
            $table->boolean('refunded')->default(false);
            $table->string('failure_code')->nullable();
            $table->text('failure_message')->nullable();

            // Description and metadata
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->string('statement_descriptor')->nullable();
            $table->string('statement_descriptor_suffix')->nullable();
            $table->string('calculated_statement_descriptor')->nullable();

            // Receipt information
            $table->string('receipt_email')->nullable();
            $table->string('receipt_number')->nullable();
            $table->text('receipt_url')->nullable();

            // Payment method details
            $table->string('payment_method_type')->nullable();
            $table->json('payment_method_details')->nullable();

            // Billing details
            $table->json('billing_details')->nullable();

            // Outcome information
            $table->json('outcome')->nullable();

            // Fraud and risk details
            $table->json('fraud_details')->nullable();
            $table->json('radar_options')->nullable();

            // Shipping information
            $table->json('shipping')->nullable();

            // Transfer information
            $table->string('transfer_group')->nullable();
            $table->json('transfer_data')->nullable();

            // Application and fees
            $table->string('application')->nullable();
            $table->string('application_fee')->nullable();
            $table->integer('application_fee_amount')->nullable();

            // Destination and on_behalf_of
            $table->string('destination')->nullable();
            $table->string('on_behalf_of')->nullable();

            // Order and source
            $table->string('order')->nullable();
            $table->string('source')->nullable();
            $table->string('source_transfer')->nullable();

            // Review and dispute
            $table->string('dispute')->nullable();
            $table->string('review')->nullable();

            // Failure balance transaction
            $table->string('failure_balance_transaction')->nullable();

            // Timestamps
            $table->timestamp('stripe_created_at')->nullable()->comment('When transaction was created in Stripe');
            $table->timestamps();

            // Indexes
            $table->index(['stripe_id']);
            $table->index(['category']);
            $table->index(['status']);
            $table->index(['financial_account']);
            $table->index(['stripe_created_at']);
        });

        // Create stripe_invoices table
        Schema::create('stripe_invoices', function (Blueprint $table) {
            $table->id();

            // Core Stripe invoice fields
            $table->string('stripe_id')->unique()->comment('Stripe invoice ID');
            $table->string('object_type')->default('invoice')->comment('Stripe object type');

            // Account information
            $table->string('account_country', 2)->nullable()->comment('Account country code');
            $table->string('account_name')->nullable()->comment('Account name');
            $table->json('account_tax_ids')->nullable()->comment('Account tax IDs');

            // Amount information
            $table->bigInteger('amount_due')->default(0)->comment('Amount due in smallest currency unit');
            $table->bigInteger('amount_paid')->default(0)->comment('Amount paid in smallest currency unit');
            $table->bigInteger('amount_overpaid')->default(0)->comment('Amount overpaid in smallest currency unit');
            $table->bigInteger('amount_remaining')->default(0)->comment('Amount remaining in smallest currency unit');
            $table->bigInteger('amount_shipping')->default(0)->comment('Shipping amount in smallest currency unit');
            $table->string('currency', 3)->default('usd')->comment('Currency code');
            $table->bigInteger('subtotal')->default(0)->comment('Subtotal in smallest currency unit');
            $table->bigInteger('subtotal_excluding_tax')->default(0)->comment('Subtotal excluding tax');
            $table->bigInteger('total')->default(0)->comment('Total amount in smallest currency unit');
            $table->bigInteger('total_excluding_tax')->default(0)->comment('Total excluding tax');
            $table->bigInteger('starting_balance')->default(0)->comment('Starting balance');
            $table->bigInteger('ending_balance')->nullable()->comment('Ending balance');

            // Customer information
            $table->string('customer')->nullable()->comment('Customer ID');
            $table->string('customer_email')->nullable()->comment('Customer email');
            $table->string('customer_name')->nullable()->comment('Customer name');
            $table->string('customer_phone')->nullable()->comment('Customer phone');
            $table->string('customer_tax_exempt')->nullable()->comment('Customer tax exempt status');
            $table->json('customer_tax_ids')->nullable()->comment('Customer tax IDs');
            $table->json('customer_address')->nullable()->comment('Customer address');
            $table->json('customer_shipping')->nullable()->comment('Customer shipping info');

            // Invoice details
            $table->string('billing_reason')->nullable()->comment('Billing reason (manual, subscription, etc.)');
            $table->string('collection_method')->nullable()->comment('Collection method');
            $table->string('status')->nullable()->comment('Invoice status');
            $table->string('number')->nullable()->comment('Invoice number');
            $table->text('description')->nullable()->comment('Invoice description');
            $table->text('footer')->nullable()->comment('Invoice footer');
            $table->string('receipt_number')->nullable()->comment('Receipt number');
            $table->string('statement_descriptor')->nullable()->comment('Statement descriptor');

            // Payment information
            $table->string('payment_intent')->nullable()->comment('Payment intent ID');
            $table->string('default_payment_method')->nullable()->comment('Default payment method');
            $table->string('default_source')->nullable()->comment('Default source');
            $table->boolean('paid')->default(false)->comment('Whether invoice is paid');
            $table->boolean('paid_out_of_band')->default(false)->comment('Paid out of band');
            $table->boolean('attempted')->default(false)->comment('Whether payment was attempted');
            $table->integer('attempt_count')->default(0)->comment('Number of payment attempts');
            $table->boolean('auto_advance')->default(false)->comment('Auto advance setting');

            // Billing period
            $table->timestamp('period_start')->nullable()->comment('Billing period start');
            $table->timestamp('period_end')->nullable()->comment('Billing period end');
            $table->timestamp('due_date')->nullable()->comment('Due date');
            $table->timestamp('next_payment_attempt')->nullable()->comment('Next payment attempt');

            // Tax information
            $table->json('automatic_tax')->nullable()->comment('Automatic tax settings');
            $table->json('default_tax_rates')->nullable()->comment('Default tax rates');
            $table->json('total_taxes')->nullable()->comment('Total taxes');
            $table->json('total_discount_amounts')->nullable()->comment('Total discount amounts');

            // Credit notes
            $table->bigInteger('pre_payment_credit_notes_amount')->default(0)->comment('Pre-payment credit notes amount');
            $table->bigInteger('post_payment_credit_notes_amount')->default(0)->comment('Post-payment credit notes amount');

            // Shipping
            $table->json('shipping_cost')->nullable()->comment('Shipping cost');
            $table->json('shipping_details')->nullable()->comment('Shipping details');

            // URLs and documents
            $table->string('hosted_invoice_url')->nullable()->comment('Hosted invoice URL');
            $table->string('invoice_pdf')->nullable()->comment('Invoice PDF URL');
            $table->string('confirmation_secret')->nullable()->comment('Confirmation secret');

            // Issuer and parent
            $table->json('issuer')->nullable()->comment('Invoice issuer');
            $table->string('parent')->nullable()->comment('Parent invoice ID');
            $table->string('from_invoice')->nullable()->comment('From invoice ID');
            $table->string('latest_revision')->nullable()->comment('Latest revision ID');

            // Payment settings
            $table->json('payment_settings')->nullable()->comment('Payment settings');

            // Transfer data
            $table->json('transfer_data')->nullable()->comment('Transfer data');

            // Test clock
            $table->string('test_clock')->nullable()->comment('Test clock ID');

            // Status transitions
            $table->timestamp('status_transitions_finalized_at')->nullable()->comment('When finalized');
            $table->timestamp('status_transitions_marked_uncollectible_at')->nullable()->comment('When marked uncollectible');
            $table->timestamp('status_transitions_paid_at')->nullable()->comment('When paid');
            $table->timestamp('status_transitions_voided_at')->nullable()->comment('When voided');

            // Other fields
            $table->json('custom_fields')->nullable()->comment('Custom fields');
            $table->json('discounts')->nullable()->comment('Discounts');
            $table->json('metadata')->nullable()->comment('Metadata');
            $table->string('application')->nullable()->comment('Application ID');
            $table->string('on_behalf_of')->nullable()->comment('On behalf of');
            $table->string('last_finalization_error')->nullable()->comment('Last finalization error');

            // Timestamps
            $table->timestamp('stripe_created_at')->nullable()->comment('When created in Stripe');
            $table->timestamp('webhooks_delivered_at')->nullable()->comment('When webhooks were delivered');
            $table->boolean('livemode')->default(false)->comment('Whether this is live mode');
            $table->timestamps();

            // Indexes
            $table->index(['stripe_id']);
            $table->index(['customer']);
            $table->index(['status']);
            $table->index(['paid']);
            $table->index(['billing_reason']);
            $table->index(['collection_method']);
            $table->index(['stripe_created_at']);
            $table->index(['period_start']);
            $table->index(['period_end']);
        });

        // Add foreign key constraints
        Schema::table('stripe_prices', function (Blueprint $table) {
            $table->foreign('product')->references('stripe_id')->on('stripe_products')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        Schema::table('stripe_products', function (Blueprint $table) {
            $table->foreign('default_price')->references('stripe_id')->on('stripe_prices')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraints first
        Schema::table('stripe_products', function (Blueprint $table) {
            $table->dropForeign(['default_price']);
        });

        Schema::table('stripe_prices', function (Blueprint $table) {
            $table->dropForeign(['product']);
        });

        // Drop tables in reverse order
        Schema::dropIfExists('stripe_invoices');
        Schema::dropIfExists('stripe_transactions');
        Schema::dropIfExists('stripe_tax_rates');
        Schema::dropIfExists('stripe_tax_codes');
        Schema::dropIfExists('stripe_coupons');
        Schema::dropIfExists('stripe_promotion_codes');
        Schema::dropIfExists('stripe_discounts');
        Schema::dropIfExists('stripe_customers');
        Schema::dropIfExists('stripe_meters');
        Schema::dropIfExists('stripe_prices');
        Schema::dropIfExists('stripe_products');
    }
};
