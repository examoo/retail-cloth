<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with('roles');

        // Filter by role if provided
        if ($request->has('role') && in_array($request->role, User::ROLES)) {
            $query->role($request->role);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:' . implode(',', User::ROLES)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign role using Spatie
        $user->syncRoles([$validated['role']]);

        return response()->json([
            'message' => 'User created successfully.',
            'user' => $user->load('roles'),
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'user' => $user->load('roles'),
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['sometimes', 'required', 'confirmed', Rules\Password::defaults()],
            'role' => ['sometimes', 'required', 'string', 'in:' . implode(',', User::ROLES)],
        ]);

        // Prevent changing own role
        if ($request->has('role') && $user->id === Auth::id()) {
            return response()->json([
                'message' => 'You cannot change your own role.',
            ], 403);
        }

        // Prevent non-super-admin from assigning super_admin role
        if ($request->has('role') && $validated['role'] === User::ROLE_SUPER_ADMIN && !Auth::user()->isSuperAdmin()) {
            return response()->json([
                'message' => 'Only super admins can assign the super admin role.',
            ], 403);
        }

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        // Extract role from validated data
        $role = $validated['role'] ?? null;
        unset($validated['role']);

        $user->update($validated);

        // Update role using Spatie if provided
        if ($role) {
            $user->syncRoles([$role]);
        }

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $user->fresh()->load('roles'),
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        // Prevent self-deletion
        if ($user->id === Auth::id()) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        // Only super admin can delete other super admins
        if ($user->isSuperAdmin() && !Auth::user()->isSuperAdmin()) {
            return response()->json([
                'message' => 'Only super admins can delete other super admins.',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
}

