<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     * @param  string  ...$permissions  Required permissions (comma-separated or multiple params)
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (!Auth::guard('admin')->check()) {
            return response()->json([
                'message' => 'Unauthorized. Authentication required.',
            ], 401);
        }

        $user = Auth::guard('admin')->user();

        // Flatten permissions
        $requiredPermissions = [];
        foreach ($permissions as $permission) {
            $requiredPermissions = array_merge($requiredPermissions, explode(',', $permission));
        }

        // Trim whitespace
        $requiredPermissions = array_map('trim', $requiredPermissions);

        // Use Spatie's hasAnyPermission method
        if (!$user->hasAnyPermission($requiredPermissions)) {
            return response()->json([
                'message' => 'Forbidden. You do not have the required permission to access this resource.',
                'required_permissions' => $requiredPermissions,
            ], 403);
        }

        return $next($request);
    }
}
