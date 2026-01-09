<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Dashboard
            'view-dashboard',

            // User Management
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',

            // Customer Management
            'view-customers',
            'create-customers',
            'edit-customers',
            'delete-customers',

            // Orders
            'view-orders',
            'create-orders',
            'edit-orders',
            'delete-orders',

            // Measurements
            'view-measurements',
            'create-measurements',
            'edit-measurements',

            // Inventory
            'view-inventory',
            'manage-inventory',

            // Products
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',

            // Product Variants
            'view-variants',
            'create-variants',
            'edit-variants',
            'delete-variants',

            // Sizes
            'view-sizes',
            'create-sizes',
            'edit-sizes',
            'delete-sizes',

            // Colors
            'view-colors',
            'create-colors',
            'edit-colors',
            'delete-colors',

            // Fabrics
            'view-fabrics',
            'create-fabrics',
            'edit-fabrics',
            'delete-fabrics',

            // Fits
            'view-fits',
            'create-fits',
            'edit-fits',
            'delete-fits',

            // Tax Classes
            'view-tax-classes',
            'create-tax-classes',
            'edit-tax-classes',
            'delete-tax-classes',

            // Stores
            'view-stores',
            'create-stores',
            'edit-stores',
            'delete-stores',

            // Reports
            'view-reports',

            // Settings
            'manage-settings',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create roles and assign permissions
        // Super Admin - gets ALL permissions
        $superAdmin = Role::findOrCreate(User::ROLE_SUPER_ADMIN);
        $superAdmin->syncPermissions(Permission::all());

        // Admin - gets most permissions except user management
        $admin = Role::findOrCreate(User::ROLE_ADMIN);
        $admin->syncPermissions([
            'view-dashboard',
            'view-customers', 'create-customers', 'edit-customers', 'delete-customers',
            'view-orders', 'create-orders', 'edit-orders', 'delete-orders',
            'view-measurements', 'create-measurements', 'edit-measurements',
            'view-inventory', 'manage-inventory',
            'view-products', 'create-products', 'edit-products', 'delete-products',
            'view-variants', 'create-variants', 'edit-variants', 'delete-variants',
            'view-sizes', 'create-sizes', 'edit-sizes', 'delete-sizes',
            'view-colors', 'create-colors', 'edit-colors', 'delete-colors',
            'view-fabrics', 'create-fabrics', 'edit-fabrics', 'delete-fabrics',
            'view-fits', 'create-fits', 'edit-fits', 'delete-fits',
            'view-tax-classes', 'create-tax-classes', 'edit-tax-classes', 'delete-tax-classes',
            'view-stores', 'create-stores', 'edit-stores', 'delete-stores',
            'view-reports',
            'manage-settings',
        ]);

        // Staff - gets operational permissions
        $staff = Role::findOrCreate(User::ROLE_STAFF);
        $staff->syncPermissions([
            'view-dashboard',
            'view-customers', 'create-customers', 'edit-customers',
            'view-orders', 'create-orders', 'edit-orders',
            'view-measurements', 'create-measurements', 'edit-measurements',
            'view-inventory',
            'view-products',
            'view-variants',
            'view-sizes', 'view-colors', 'view-fabrics', 'view-fits',
        ]);

        // Tailor - gets limited permissions
        $tailor = Role::findOrCreate(User::ROLE_TAILOR);
        $tailor->syncPermissions([
            'view-dashboard',
            'view-orders',
            'view-measurements', 'edit-measurements',
        ]);

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
