<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && ! Auth::user()->is_approved) {
            // If they are on the pending page, let them through
            if ($request->routeIs('auth.pending') ||
                $request->routeIs('logout') ||
                $request->routeIs('verification.*')) {
                return $next($request);
            }

            return Inertia::render('auth/PendingApproval')->toResponse($request);
        }

        return $next($request);
    }
}
