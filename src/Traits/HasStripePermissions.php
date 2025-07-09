<?php

namespace Fullstack\StripeProductManager\Traits;

use Spatie\Permission\Traits\HasRoles;

trait HasStripePermissions
{
    use HasRoles;

    /**
     * Get the guard names that this trait supports
     */
    protected function getStripeGuards(): array
    {
        return ['web', 'tenant-admin', 'super-admin'];
    }

    /**
     * Check if user can view Stripe products
     */
    public function canViewStripeProducts(): bool
    {
        return $this->hasPermissionTo('stripe.products.view');
    }

    /**
     * Check if user can create Stripe products
     */
    public function canCreateStripeProducts(): bool
    {
        return $this->hasPermissionTo('stripe.products.create');
    }

    /**
     * Check if user can edit Stripe products
     */
    public function canEditStripeProducts(): bool
    {
        return $this->hasPermissionTo('stripe.products.edit');
    }

    /**
     * Check if user can delete Stripe products
     */
    public function canDeleteStripeProducts(): bool
    {
        return $this->hasPermissionTo('stripe.products.delete');
    }

    /**
     * Check if user can sync Stripe products
     */
    public function canSyncStripeProducts(): bool
    {
        return $this->hasPermissionTo('stripe.products.sync');
    }

    /**
     * Check if user can view Stripe prices
     */
    public function canViewStripePrices(): bool
    {
        return $this->hasPermissionTo('stripe.prices.view');
    }

    /**
     * Check if user can create Stripe prices
     */
    public function canCreateStripePrices(): bool
    {
        return $this->hasPermissionTo('stripe.prices.create');
    }

    /**
     * Check if user can edit Stripe prices
     */
    public function canEditStripePrices(): bool
    {
        return $this->hasPermissionTo('stripe.prices.edit');
    }

    /**
     * Check if user can delete Stripe prices
     */
    public function canDeleteStripePrices(): bool
    {
        return $this->hasPermissionTo('stripe.prices.delete');
    }

    /**
     * Check if user can sync Stripe prices
     */
    public function canSyncStripePrices(): bool
    {
        return $this->hasPermissionTo('stripe.prices.sync');
    }

    /**
     * Check if user can view Stripe customers
     */
    public function canViewStripeCustomers(): bool
    {
        return $this->hasPermissionTo('stripe.customers.view');
    }

    /**
     * Check if user can create Stripe customers
     */
    public function canCreateStripeCustomers(): bool
    {
        return $this->hasPermissionTo('stripe.customers.create');
    }

    /**
     * Check if user can edit Stripe customers
     */
    public function canEditStripeCustomers(): bool
    {
        return $this->hasPermissionTo('stripe.customers.edit');
    }

    /**
     * Check if user can delete Stripe customers
     */
    public function canDeleteStripeCustomers(): bool
    {
        return $this->hasPermissionTo('stripe.customers.delete');
    }

    /**
     * Check if user can sync Stripe customers
     */
    public function canSyncStripeCustomers(): bool
    {
        return $this->hasPermissionTo('stripe.customers.sync');
    }

    /**
     * Check if user can view Stripe invoices
     */
    public function canViewStripeInvoices(): bool
    {
        return $this->hasPermissionTo('stripe.invoices.view');
    }

    /**
     * Check if user can create Stripe invoices
     */
    public function canCreateStripeInvoices(): bool
    {
        return $this->hasPermissionTo('stripe.invoices.create');
    }

    /**
     * Check if user can edit Stripe invoices
     */
    public function canEditStripeInvoices(): bool
    {
        return $this->hasPermissionTo('stripe.invoices.edit');
    }

    /**
     * Check if user can delete Stripe invoices
     */
    public function canDeleteStripeInvoices(): bool
    {
        return $this->hasPermissionTo('stripe.invoices.delete');
    }

    /**
     * Check if user can sync Stripe invoices
     */
    public function canSyncStripeInvoices(): bool
    {
        return $this->hasPermissionTo('stripe.invoices.sync');
    }

    /**
     * Check if user can view Stripe transactions
     */
    public function canViewStripeTransactions(): bool
    {
        return $this->hasPermissionTo('stripe.transactions.view');
    }

    /**
     * Check if user can create Stripe transactions
     */
    public function canCreateStripeTransactions(): bool
    {
        return $this->hasPermissionTo('stripe.transactions.create');
    }

    /**
     * Check if user can edit Stripe transactions
     */
    public function canEditStripeTransactions(): bool
    {
        return $this->hasPermissionTo('stripe.transactions.edit');
    }

    /**
     * Check if user can delete Stripe transactions
     */
    public function canDeleteStripeTransactions(): bool
    {
        return $this->hasPermissionTo('stripe.transactions.delete');
    }

    /**
     * Check if user can sync Stripe transactions
     */
    public function canSyncStripeTransactions(): bool
    {
        return $this->hasPermissionTo('stripe.transactions.sync');
    }

    /**
     * Check if user can view Stripe coupons
     */
    public function canViewStripeCoupons(): bool
    {
        return $this->hasPermissionTo('stripe.coupons.view');
    }

    /**
     * Check if user can create Stripe coupons
     */
    public function canCreateStripeCoupons(): bool
    {
        return $this->hasPermissionTo('stripe.coupons.create');
    }

    /**
     * Check if user can edit Stripe coupons
     */
    public function canEditStripeCoupons(): bool
    {
        return $this->hasPermissionTo('stripe.coupons.edit');
    }

    /**
     * Check if user can delete Stripe coupons
     */
    public function canDeleteStripeCoupons(): bool
    {
        return $this->hasPermissionTo('stripe.coupons.delete');
    }

    /**
     * Check if user can sync Stripe coupons
     */
    public function canSyncStripeCoupons(): bool
    {
        return $this->hasPermissionTo('stripe.coupons.sync');
    }

    /**
     * Check if user can view Stripe discounts
     */
    public function canViewStripeDiscounts(): bool
    {
        return $this->hasPermissionTo('stripe.discounts.view');
    }

    /**
     * Check if user can create Stripe discounts
     */
    public function canCreateStripeDiscounts(): bool
    {
        return $this->hasPermissionTo('stripe.discounts.create');
    }

    /**
     * Check if user can edit Stripe discounts
     */
    public function canEditStripeDiscounts(): bool
    {
        return $this->hasPermissionTo('stripe.discounts.edit');
    }

    /**
     * Check if user can delete Stripe discounts
     */
    public function canDeleteStripeDiscounts(): bool
    {
        return $this->hasPermissionTo('stripe.discounts.delete');
    }

    /**
     * Check if user can sync Stripe discounts
     */
    public function canSyncStripeDiscounts(): bool
    {
        return $this->hasPermissionTo('stripe.discounts.sync');
    }

    /**
     * Check if user can view Stripe promotion codes
     */
    public function canViewStripePromotionCodes(): bool
    {
        return $this->hasPermissionTo('stripe.promotion_codes.view');
    }

    /**
     * Check if user can create Stripe promotion codes
     */
    public function canCreateStripePromotionCodes(): bool
    {
        return $this->hasPermissionTo('stripe.promotion_codes.create');
    }

    /**
     * Check if user can edit Stripe promotion codes
     */
    public function canEditStripePromotionCodes(): bool
    {
        return $this->hasPermissionTo('stripe.promotion_codes.edit');
    }

    /**
     * Check if user can delete Stripe promotion codes
     */
    public function canDeleteStripePromotionCodes(): bool
    {
        return $this->hasPermissionTo('stripe.promotion_codes.delete');
    }

    /**
     * Check if user can sync Stripe promotion codes
     */
    public function canSyncStripePromotionCodes(): bool
    {
        return $this->hasPermissionTo('stripe.promotion_codes.sync');
    }

    /**
     * Check if user can view Stripe tax codes
     */
    public function canViewStripeTaxCodes(): bool
    {
        return $this->hasPermissionTo('stripe.tax_codes.view');
    }

    /**
     * Check if user can create Stripe tax codes
     */
    public function canCreateStripeTaxCodes(): bool
    {
        return $this->hasPermissionTo('stripe.tax_codes.create');
    }

    /**
     * Check if user can edit Stripe tax codes
     */
    public function canEditStripeTaxCodes(): bool
    {
        return $this->hasPermissionTo('stripe.tax_codes.edit');
    }

    /**
     * Check if user can delete Stripe tax codes
     */
    public function canDeleteStripeTaxCodes(): bool
    {
        return $this->hasPermissionTo('stripe.tax_codes.delete');
    }

    /**
     * Check if user can sync Stripe tax codes
     */
    public function canSyncStripeTaxCodes(): bool
    {
        return $this->hasPermissionTo('stripe.tax_codes.sync');
    }

    /**
     * Check if user can view Stripe tax rates
     */
    public function canViewStripeTaxRates(): bool
    {
        return $this->hasPermissionTo('stripe.tax_rates.view');
    }

    /**
     * Check if user can create Stripe tax rates
     */
    public function canCreateStripeTaxRates(): bool
    {
        return $this->hasPermissionTo('stripe.tax_rates.create');
    }

    /**
     * Check if user can edit Stripe tax rates
     */
    public function canEditStripeTaxRates(): bool
    {
        return $this->hasPermissionTo('stripe.tax_rates.edit');
    }

    /**
     * Check if user can delete Stripe tax rates
     */
    public function canDeleteStripeTaxRates(): bool
    {
        return $this->hasPermissionTo('stripe.tax_rates.delete');
    }

    /**
     * Check if user can sync Stripe tax rates
     */
    public function canSyncStripeTaxRates(): bool
    {
        return $this->hasPermissionTo('stripe.tax_rates.sync');
    }

    /**
     * Check if user has admin access to Stripe
     */
    public function hasStripeAdminAccess(): bool
    {
        return $this->hasPermissionTo('stripe.admin.access');
    }

    /**
     * Check if user can access Stripe settings
     */
    public function canAccessStripeSettings(): bool
    {
        return $this->hasPermissionTo('stripe.admin.settings');
    }

    /**
     * Check if user can access Stripe reports
     */
    public function canAccessStripeReports(): bool
    {
        return $this->hasPermissionTo('stripe.admin.reports');
    }

    /**
     * Check if user can sync all Stripe data
     */
    public function canSyncAllStripeData(): bool
    {
        return $this->hasPermissionTo('stripe.admin.sync_all');
    }

    /**
     * Check if user has any Stripe role
     */
    public function hasStripeRole(): bool
    {
        return $this->hasAnyRole(['stripe-admin', 'stripe-manager', 'stripe-viewer']);
    }

    /**
     * Check if user is a Stripe admin
     */
    public function isStripeAdmin(): bool
    {
        return $this->hasRole('stripe-admin');
    }

    /**
     * Check if user is a Stripe manager
     */
    public function isStripeManager(): bool
    {
        return $this->hasRole('stripe-manager');
    }

    /**
     * Check if user is a Stripe viewer
     */
    public function isStripeViewer(): bool
    {
        return $this->hasRole('stripe-viewer');
    }

    // Guard-specific methods

    /**
     * Check if user has Stripe admin role for tenant-admin guard
     */
    public function isStripeAdminForTenant(): bool
    {
        return $this->hasRole('stripe-admin', 'tenant-admin');
    }

    /**
     * Check if user has Stripe admin role for super-admin guard
     */
    public function isStripeAdminForSuper(): bool
    {
        return $this->hasRole('stripe-admin', 'super-admin');
    }

    /**
     * Check if user has Stripe manager role for tenant-admin guard
     */
    public function isStripeManagerForTenant(): bool
    {
        return $this->hasRole('stripe-manager', 'tenant-admin');
    }

    /**
     * Check if user has Stripe manager role for super-admin guard
     */
    public function isStripeManagerForSuper(): bool
    {
        return $this->hasRole('stripe-manager', 'super-admin');
    }

    /**
     * Check if user has Stripe viewer role for tenant-admin guard
     */
    public function isStripeViewerForTenant(): bool
    {
        return $this->hasRole('stripe-viewer', 'tenant-admin');
    }

    /**
     * Check if user has Stripe viewer role for super-admin guard
     */
    public function isStripeViewerForSuper(): bool
    {
        return $this->hasRole('stripe-viewer', 'super-admin');
    }

    /**
     * Check if user has any Stripe role for tenant-admin guard
     */
    public function hasStripeRoleForTenant(): bool
    {
        return $this->hasAnyRole(['stripe-admin', 'stripe-manager', 'stripe-viewer'], 'tenant-admin');
    }

    /**
     * Check if user has any Stripe role for super-admin guard
     */
    public function hasStripeRoleForSuper(): bool
    {
        return $this->hasAnyRole(['stripe-admin', 'stripe-manager', 'stripe-viewer'], 'super-admin');
    }

    /**
     * Check if user can view Stripe products for tenant-admin guard
     */
    public function canViewStripeProductsForTenant(): bool
    {
        return $this->hasPermissionTo('stripe.products.view', 'tenant-admin');
    }

    /**
     * Check if user can view Stripe products for super-admin guard
     */
    public function canViewStripeProductsForSuper(): bool
    {
        return $this->hasPermissionTo('stripe.products.view', 'super-admin');
    }

    /**
     * Check if user can create Stripe products for tenant-admin guard
     */
    public function canCreateStripeProductsForTenant(): bool
    {
        return $this->hasPermissionTo('stripe.products.create', 'tenant-admin');
    }

    /**
     * Check if user can create Stripe products for super-admin guard
     */
    public function canCreateStripeProductsForSuper(): bool
    {
        return $this->hasPermissionTo('stripe.products.create', 'super-admin');
    }
}
