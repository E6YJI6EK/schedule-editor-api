<?php

namespace Tests\Feature\Services;

use App\Enums\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserService();
    }

    public function test_register_creates_user_with_employee_role(): void
    {
        $result = $this->service->register([
            'name' => 'New Employee',
            'email' => 'emp@example.com',
            'password' => 'secret123',
        ]);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('New Employee', $result->name);
        $this->assertEquals('emp@example.com', $result->email);
        $this->assertEquals(Role::Employee, $result->role);
        $this->assertDatabaseHas('users', ['email' => 'emp@example.com', 'role' => Role::Employee]);
    }

    public function test_register_always_assigns_employee_role(): void
    {
        $result = $this->service->register([
            'name' => 'Sneaky User',
            'email' => 'sneaky@example.com',
            'password' => 'password',
        ]);

        $this->assertEquals(Role::Employee, $result->role);
    }

    public function test_employees_returns_only_employees(): void
    {
        User::factory()->create(['role' => Role::Admin]);
        User::factory()->create(['role' => Role::Employee]);
        User::factory()->create(['role' => Role::Employee]);

        $result = $this->service->employees();

        $this->assertCount(2, $result);
        $result->each(fn($u) => $this->assertEquals(Role::Employee, $u->role));
    }

    public function test_employees_returns_empty_when_none(): void
    {
        User::factory()->create(['role' => Role::Admin]);

        $result = $this->service->employees();

        $this->assertCount(0, $result);
    }

    public function test_delete_employee_deletes_and_returns_null(): void
    {
        $employee = User::factory()->create(['role' => Role::Employee]);

        $result = $this->service->deleteEmployee($employee->id);

        $this->assertNull($result);
        $this->assertDatabaseMissing('users', ['id' => $employee->id]);
    }

    public function test_delete_employee_returns_not_found_for_missing_user(): void
    {
        $result = $this->service->deleteEmployee(999);

        $this->assertEquals('not_found', $result);
    }

    public function test_delete_employee_returns_forbidden_for_admin(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);

        $result = $this->service->deleteEmployee($admin->id);

        $this->assertEquals('forbidden', $result);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
