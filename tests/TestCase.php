<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        // Create a super admin user and authenticate
        $superAdminRole = Role::where('slug', 'super-admin')->orWhere('name', 'Super Admin')->first();
        $admin = User::factory()->create([
            'email' => 'admin_'.\Str::random(8).'@smartpos.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $admin->syncRoles([$superAdminRole]);
        $this->actingAs($admin);
    }
}
