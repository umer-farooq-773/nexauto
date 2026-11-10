<?php

namespace App\Http\Middleware;

use App\Constants\Constants;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictDriverToPortal
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            $isDriver = $user->hasRole(Constants::DRIVER) || (
                $user->driverDetail !== null && !$user->hasAnyRole([
                    Constants::SUPERADMIN,
                    Constants::ADMIN,
                    Constants::CHAMPS_MANAGEMENT,
                    Constants::CLIENT_DIRECTORS,
                    Constants::CLIENT_MANAGEMENT,
                ])
            );

            if ($isDriver) {
                // If attempting to access backoffice /my-account routes, strictly redirect to driver portal
                if ($request->is('my-account*')) {
                    return redirect()->route('driver.portal');
                }

                // Allow driver portal routes, logout, and driver AJAX endpoints
                if ($request->is('driver-panel*') || $request->routeIs('driver.*') || $request->is('logout') || $request->routeIs('logout')) {
                    return $next($request);
                }

                // Any other backoffice route: strictly redirect to driver portal
                return redirect()->route('driver.portal');
            }
        }

        return $next($request);
    }
}

