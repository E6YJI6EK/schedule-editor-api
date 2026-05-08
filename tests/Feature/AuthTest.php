<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // --- Register (admin only) ---

    public function test_register_success_as_admin(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);

        $response = $this->actingAs($admin)->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 201)
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'role']]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'role' => Role::Employee->value,
        ]);
    }

    public function test_register_forbidden_for_employee(): void
    {
        $employee = User::factory()->create(['role' => Role::Employee]);

        $response = $this->actingAs($employee)->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    public function test_register_requires_auth(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(401);
    }

    public function test_register_requires_name(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);

        $response = $this->actingAs($admin)->postJson('/api/auth/register', [
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    public function test_register_name_min_length(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);

        $response = $this->actingAs($admin)->postJson('/api/auth/register', [
            'name' => 'J',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    public function test_register_requires_email(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);

        $response = $this->actingAs($admin)->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_register_requires_valid_email(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);

        $response = $this->actingAs($admin)->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_register_email_must_be_unique(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->actingAs($admin)->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_register_password_min_length(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);

        $response = $this->actingAs($admin)->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['password']);
    }

    public function test_register_password_confirmation_mismatch(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);

        $response = $this->actingAs($admin)->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpass',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['password']);
    }

    // --- Login (public) ---

    public function test_login_success(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonStructure(['data' => ['id', 'name', 'email']]);

        $this->assertAuthenticated();
    }

    public function test_login_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)->assertJsonPath('success', false);
    }

    public function test_login_nonexistent_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)->assertJsonPath('success', false);
    }

    public function test_login_requires_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_password(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['password']);
    }

    public function test_login_requires_valid_email_format(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    // --- Logout (authenticated) ---

    public function test_logout_success(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/auth/logout');

        $response->assertStatus(200)->assertJsonPath('success', true);
        $this->assertGuest();
    }

    public function test_logout_requires_auth(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }

    // --- Delete employee (admin only) ---

    public function test_delete_employee_success_as_admin(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);
        $employee = User::factory()->create(['role' => Role::Employee]);

        $response = $this->actingAs($admin)->deleteJson("/api/employees/{$employee->id}");

        $response->assertStatus(200)->assertJsonPath('success', true);
        $this->assertDatabaseMissing('users', ['id' => $employee->id]);
    }

    public function test_delete_employee_forbidden_for_employee(): void
    {
        $actor = User::factory()->create(['role' => Role::Employee]);
        $target = User::factory()->create(['role' => Role::Employee]);

        $response = $this->actingAs($actor)->deleteJson("/api/employees/{$target->id}");

        $response->assertStatus(403);
    }

    public function test_delete_employee_requires_auth(): void
    {
        $employee = User::factory()->create(['role' => Role::Employee]);

        $response = $this->deleteJson("/api/employees/{$employee->id}");

        $response->assertStatus(401);
    }

    public function test_delete_employee_not_found(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);

        $response = $this->actingAs($admin)->deleteJson('/api/employees/99999');

        $response->assertStatus(404)->assertJsonPath('success', false);
    }

    public function test_delete_admin_is_forbidden(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);
        $otherAdmin = User::factory()->create(['role' => Role::Admin]);

        $response = $this->actingAs($admin)->deleteJson("/api/employees/{$otherAdmin->id}");

        $response->assertStatus(403)->assertJsonPath('success', false);
    }

    // --- Me (authenticated) ---

    public function test_me_returns_current_user(): void
    {
        $user = User::factory()->create(['role' => Role::Employee]);

        $this->actingAs($user)->getJson('/api/auth/me')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_me_requires_auth(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }

    // --- Employees list (admin only) ---

    public function test_employees_returns_list_as_admin(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);
        User::factory()->create(['role' => Role::Employee]);
        User::factory()->create(['role' => Role::Employee]);

        $this->actingAs($admin)->getJson('/api/employees')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_employees_requires_auth(): void
    {
        $this->getJson('/api/employees')->assertStatus(401);
    }

    public function test_employees_requires_admin(): void
    {
        $employee = User::factory()->create(['role' => Role::Employee]);

        $this->actingAs($employee)->getJson('/api/employees')->assertStatus(403);
    }
}
