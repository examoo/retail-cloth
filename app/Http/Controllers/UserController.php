<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'role' => $request->get('role'),
            'search' => $request->get('search'),
        ];

        $users = $this->userService->getUsers($filters, $request->get('per_page', 15));

        return response()->json($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(UserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return response()->json([
            'message' => 'User created successfully.',
            'user' => $user,
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
    public function update(UserRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        // Prevent changing own role
        if (isset($validated['role']) && $user->id === Auth::guard('admin')->id()) {
            return response()->json([
                'message' => 'You cannot change your own role.',
            ], 403);
        }

        // Prevent non-super-admin from assigning super_admin role
        if (isset($validated['role']) && $validated['role'] === User::ROLE_SUPER_ADMIN && !Auth::guard('admin')->user()->isSuperAdmin()) {
            return response()->json([
                'message' => 'Only super admins can assign the super admin role.',
            ], 403);
        }

        $user = $this->userService->updateUser($user, $validated);

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $user,
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        // Prevent self-deletion
        if ($user->id === Auth::guard('admin')->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        // Only super admin can delete other super admins
        if ($user->isSuperAdmin() && !Auth::guard('admin')->user()->isSuperAdmin()) {
            return response()->json([
                'message' => 'Only super admins can delete other super admins.',
            ], 403);
        }

        $this->userService->deleteUser($user);

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
}


