<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        // Check if the authenticated user has the required role
        if ($request->user()->role !== $role) {
            return response()->json(['message' => 'Forbidden'], 403); // Forbidden if not admin
        }

        return $next($request); // Allow the request to proceed
    }
}
