<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_redirects_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');

        $response = $this->get('/users');
        $response->assertRedirect('/login');

        $response = $this->get('/settings');
        $response->assertRedirect('/login');
    }

    public function test_user_role_view_only_access(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/equipment');
        $response->assertStatus(200);

        // Blocked from user management and settings
        $response = $this->actingAs($user)->get('/users');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('/settings');
        $response->assertStatus(403);

        // Blocked from posting telemetry
        $response = $this->actingAs($user)->post('/telemetry/generate');
        $response->assertStatus(403);
    }

    public function test_admin_role_limited_access(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get('/equipment');
        $response->assertStatus(200);

        // Blocked from user management and settings
        $response = $this->actingAs($admin)->get('/users');
        $response->assertStatus(403);

        $response = $this->actingAs($admin)->get('/settings');
        $response->assertStatus(403);

        // Allowed to post telemetry
        $response = $this->actingAs($admin)->post('/telemetry/generate', ['type' => 'normal']);
        $response->assertStatus(302); // Redirect back
    }

    public function test_system_admin_full_access(): void
    {
        $sysAdmin = User::factory()->create(['role' => 'system_admin']);

        $response = $this->actingAs($sysAdmin)->get('/users');
        $response->assertStatus(200);

        $response = $this->actingAs($sysAdmin)->get('/settings');
        $response->assertStatus(200);
    }
}
