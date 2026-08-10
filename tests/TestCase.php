<?php

namespace Tests;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Roles and permissions are part of the application's baseline: without
     * them even the public booking pages are closed. So every test that
     * refreshes the database starts out with them.
     */
    protected bool $seed = true;

    /**
     * @var class-string<Seeder>
     */
    protected string $seeder = RolesAndPermissionsSeeder::class;
}
