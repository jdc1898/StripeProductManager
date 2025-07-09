<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StripeProductManagerPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions for Stripe entities
        $permissions = [
            // Product permissions
            'stripe.products.view',
            'stripe.products.create',
            'stripe.products.edit',
            'stripe.products.delete',
            'stripe.products.sync',

            // Price permissions
            'stripe.prices.view',
            'stripe.prices.create',
            'stripe.prices.edit',
            'stripe.prices.delete',
            'stripe.prices.sync',

            // Customer permissions
            'stripe.customers.view',
            'stripe.customers.create',
            'stripe.customers.edit',
            'stripe.customers.delete',
            'stripe.customers.sync',

            // Invoice permissions
            'stripe.invoices.view',
            'stripe.invoices.create',
            'stripe.invoices.edit',
            'stripe.invoices.delete',
            'stripe.invoices.sync',

            // Transaction permissions
            'stripe.transactions.view',
            'stripe.transactions.create',
            'stripe.transactions.edit',
            'stripe.transactions.delete',
            'stripe.transactions.sync',

            // Coupon permissions
            'stripe.coupons.view',
            'stripe.coupons.create',
            'stripe.coupons.edit',
            'stripe.coupons.delete',
            'stripe.coupons.sync',

            // Discount permissions
            'stripe.discounts.view',
            'stripe.discounts.create',
            'stripe.discounts.edit',
            'stripe.discounts.delete',
            'stripe.discounts.sync',

            // Promotion code permissions
            'stripe.promotion_codes.view',
            'stripe.promotion_codes.create',
            'stripe.promotion_codes.edit',
            'stripe.promotion_codes.delete',
            'stripe.promotion_codes.sync',

            // Tax permissions
            'stripe.tax_codes.view',
            'stripe.tax_codes.create',
            'stripe.tax_codes.edit',
            'stripe.tax_codes.delete',
            'stripe.tax_codes.sync',

            'stripe.tax_rates.view',
            'stripe.tax_rates.create',
            'stripe.tax_rates.edit',
            'stripe.tax_rates.delete',
            'stripe.tax_rates.sync',

            // Admin permissions
            'stripe.admin.access',
            'stripe.admin.settings',
            'stripe.admin.reports',
            'stripe.admin.sync_all',
        ];

        // Create permissions for all guards
        $guards = ['web', 'tenant-admin', 'super-admin'];

        foreach ($guards as $guard) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
            }
        }

        // Create roles and assign permissions for each guard
        $this->createStripeAdminRole();
        $this->createStripeManagerRole();
        $this->createStripeViewerRole();
    }

    /**
     * Create Stripe Admin role with all permissions
     */
    private function createStripeAdminRole(): void
    {
        $guards = ['web', 'tenant-admin', 'super-admin'];

        foreach ($guards as $guard) {
            $role = Role::firstOrCreate(['name' => 'stripe-admin', 'guard_name' => $guard]);

            $permissions = Permission::where('name', 'like', 'stripe.%')
                ->where('guard_name', $guard)
                ->get();
            $role->syncPermissions($permissions);
        }
    }

    /**
     * Create Stripe Manager role with limited permissions
     */
    private function createStripeManagerRole(): void
    {
        $guards = ['web', 'tenant-admin', 'super-admin'];

        $managerPermissions = [
            'stripe.products.view',
            'stripe.products.create',
            'stripe.products.edit',
            'stripe.prices.view',
            'stripe.prices.create',
            'stripe.prices.edit',
            'stripe.customers.view',
            'stripe.customers.create',
            'stripe.customers.edit',
            'stripe.invoices.view',
            'stripe.invoices.create',
            'stripe.invoices.edit',
            'stripe.transactions.view',
            'stripe.coupons.view',
            'stripe.coupons.create',
            'stripe.coupons.edit',
            'stripe.discounts.view',
            'stripe.discounts.create',
            'stripe.discounts.edit',
            'stripe.promotion_codes.view',
            'stripe.promotion_codes.create',
            'stripe.promotion_codes.edit',
            'stripe.tax_codes.view',
            'stripe.tax_rates.view',
            'stripe.admin.access',
        ];

        foreach ($guards as $guard) {
            $role = Role::firstOrCreate(['name' => 'stripe-manager', 'guard_name' => $guard]);

            $permissions = Permission::whereIn('name', $managerPermissions)
                ->where('guard_name', $guard)
                ->get();
            $role->syncPermissions($permissions);
        }
    }

    /**
     * Create Stripe Viewer role with read-only permissions
     */
    private function createStripeViewerRole(): void
    {
        $guards = ['web', 'tenant-admin', 'super-admin'];

        $viewerPermissions = [
            'stripe.products.view',
            'stripe.prices.view',
            'stripe.customers.view',
            'stripe.invoices.view',
            'stripe.transactions.view',
            'stripe.coupons.view',
            'stripe.discounts.view',
            'stripe.promotion_codes.view',
            'stripe.tax_codes.view',
            'stripe.tax_rates.view',
        ];

        foreach ($guards as $guard) {
            $role = Role::firstOrCreate(['name' => 'stripe-viewer', 'guard_name' => $guard]);

            $permissions = Permission::whereIn('name', $viewerPermissions)
                ->where('guard_name', $guard)
                ->get();
            $role->syncPermissions($permissions);
        }
    }
}
