<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Get paginated list of users with optional filters.
     */
    public function getUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::with('roles');

        // Filter by role
        if (!empty($filters['role']) && in_array($filters['role'], User::ROLES)) {
            $query->role($filters['role']);
        }

        // Search by name or email
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Create a new user.
     */
    public function createUser(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Assign role using Spatie
        if (!empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return $user->load('roles');
    }

    /**
     * Update an existing user.
     */
    public function updateUser(User $user, array $data): User
    {
        $updateData = [];

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }

        if (isset($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        // Update role using Spatie if provided
        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return $user->fresh()->load('roles');
    }

    /**
     * Delete a user.
     */
    public function deleteUser(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Get a user by ID.
     */
    public function getUserById(int $id): ?User
    {
        return User::with('roles')->find($id);
    }
}
