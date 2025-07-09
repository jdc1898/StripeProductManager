<?php

namespace Fullstack\StripeProductManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StripeGuardMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $guard = 'web'): Response
    {
        // Validate guard
        $allowedGuards = ['web', 'tenant-admin', 'super-admin'];
        if (! in_array($guard, $allowedGuards)) {
            abort(403, 'Invalid guard specified');
        }

        // Check if user is authenticated with the specified guard
        if (! Auth::guard($guard)->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            // Redirect to appropriate login based on guard
            switch ($guard) {
                case 'tenant-admin':
                    return redirect()->route('tenant-admin.login');
                case 'super-admin':
                    return redirect()->route('super-admin.login');
                default:
                    return redirect()->route('login');
            }
        }

        // Check if user has any Stripe role for this guard
        $user = Auth::guard($guard)->user();
        if (! $user->hasStripeRole()) {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        return $next($request);
    }
}
