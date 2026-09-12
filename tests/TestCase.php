<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;
use App\Models\Role;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        // Create a super admin user and authenticate
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $admin = User::factory()->create([
            'email' => 'admin_' . \Str::random(8) . '@smartpos.com',
            'password' => bcrypt('password'),
            'role_id' => $superAdminRole->id,
            'is_active' => true,
        ]);
        $this->actingAs($admin);
    }
}
