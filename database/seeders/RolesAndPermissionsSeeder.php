<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // === Permissions ===
        $permissions = [
            'create booking',
            'view own bookings',
            'access dashboard',
            'view all bookings',
            'edit bookings',
            'cancel bookings',
            'create bookings for customers',
            'check in bookings',
            'check out bookings',
            'approve bookings',
            'reject bookings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Re-flush after creating (safe if using WithoutModelEvents)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // === Roles ===

        // Customer
        $customer = Role::firstOrCreate(['name' => 'customer']);
        $customer->syncPermissions([
            'create booking',
            'view own bookings',
        ]);

        // Receptionist
        $receptionist = Role::firstOrCreate(['name' => 'receptionist']);
        $receptionist->syncPermissions([
            'access dashboard',
            'view all bookings',
            'edit bookings',
            'cancel bookings',
            'create bookings for customers',
            'check in bookings',
            'check out bookings',
            'create booking',
        ]);

        // Administrator — everything the receptionist can do, plus approve/reject
        $administrator = Role::firstOrCreate(['name' => 'administrator']);
        $administrator->syncPermissions([
            ...$receptionist->permissions->pluck('name')->toArray(),
            'approve bookings',
            'reject bookings',
        ]);
    }
}
