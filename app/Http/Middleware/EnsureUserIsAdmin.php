<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated and has the admin role
        if ($request->user() && $request->user()->role === 'admin') {
            return $next($request);
        }

        // Return a forbidden response for non-admin users
        return response()->json([
            'message' => 'Forbidden. Admin access required.'
        ], 403);
    }
}
