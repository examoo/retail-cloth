<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     * @param  string  ...$roles  Allowed roles (comma-separated or multiple params)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::guard('admin')->check()) {
            return response()->json([
                'message' => 'Unauthorized. Authentication required.',
            ], 401);
        }

        $user = Auth::guard('admin')->user();

        // Flatten roles (support both "role:admin,staff" and "role:admin|role:staff" syntax)
        $allowedRoles = [];
        foreach ($roles as $role) {
            $allowedRoles = array_merge($allowedRoles, explode(',', $role));
        }

        // Use Spatie's hasAnyRole method
        if (!$user->hasAnyRole($allowedRoles)) {
            return response()->json([
                'message' => 'Forbidden. You do not have the required role to access this resource.',
                'required_roles' => $allowedRoles,
                'your_roles' => $user->getRoleNames(),
            ], 403);
        }

        return $next($request);
    }
}
