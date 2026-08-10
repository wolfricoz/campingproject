<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
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
            'manage news',
            'view customers',
            'manage locations',
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
            'manage news',
            'view customers',
        ]);

        // Administrator — everything the receptionist can do, plus approve/reject
        $administrator = Role::firstOrCreate(['name' => 'administrator']);
        $administrator->syncPermissions([
            ...$receptionist->permissions->pluck('name')->toArray(),
            'approve bookings',
            'reject bookings',
            'manage locations',
        ]);

        $this->giveExistingUsersTheCustomerRole($customer);
    }

    /**
     * Everybody who signs up is a customer, but accounts that predate the roles
     * would be left without one. Those are brought in line here, so the public
     * routes keep working for them.
     */
    private function giveExistingUsersTheCustomerRole(Role $customer): void
    {
        User::doesntHave('roles')->each(function (User $user) use ($customer): void {
            $user->assignRole($customer);
        });
    }
}
