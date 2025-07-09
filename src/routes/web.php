<?php

use Fullstack\StripeProductManager\Models\StripeCustomer;
use Fullstack\StripeProductManager\Models\StripePrice;
use Fullstack\StripeProductManager\Models\StripeProduct;
use Illuminate\Support\Facades\Route;

// Package-specific routes
Route::prefix('stripe-product-manager')->name('stripe-product-manager.')->group(function () {

    // Public routes (if any)
    Route::get('/status', function () {
        return response()->json([
            'package' => 'Stripe Product Manager',
            'version' => '0.0.1',
            'status' => 'active',
            'models' => [
                'StripeProduct' => class_exists(StripeProduct::class),
                'StripePrice' => class_exists(StripePrice::class),
                'StripeCustomer' => class_exists(StripeCustomer::class),
            ],
        ]);
    })->name('status');

    // Protected routes with web guard
    Route::middleware(['stripe.guard:web'])->group(function () {
        Route::get('/products', function () {
            return response()->json([
                'message' => 'Stripe products endpoint',
                'products' => StripeProduct::count(),
            ]);
        })->name('products.index');

        Route::get('/prices', function () {
            return response()->json([
                'message' => 'Stripe prices endpoint',
                'prices' => StripePrice::count(),
            ]);
        })->name('prices.index');

        Route::get('/customers', function () {
            return response()->json([
                'message' => 'Stripe customers endpoint',
                'customers' => StripeCustomer::count(),
            ]);
        })->name('customers.index');
    });

    // Tenant admin routes
    Route::middleware(['stripe.guard:tenant-admin'])->prefix('tenant')->name('tenant.')->group(function () {
        Route::get('/dashboard', function () {
            return response()->json([
                'message' => 'Tenant admin dashboard',
                'user' => auth()->guard('tenant-admin')->user(),
            ]);
        })->name('dashboard');
    });

    // Super admin routes
    Route::middleware(['stripe.guard:super-admin'])->prefix('super')->name('super.')->group(function () {
        Route::get('/dashboard', function () {
            return response()->json([
                'message' => 'Super admin dashboard',
                'user' => auth()->guard('super-admin')->user(),
            ]);
        })->name('dashboard');
    });
});
