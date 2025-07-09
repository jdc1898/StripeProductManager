<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeWebhookController;
use Laravel\Cashier\Http\Middleware\VerifyWebhookSignature;

// Stripe webhook route
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handleWebhook'])
  ->middleware(VerifyWebhookSignature::class)
  ->name('stripe-webhook');
