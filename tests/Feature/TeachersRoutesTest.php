<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Discipline;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeachersRoutesTest extends TestCase
{
    use RefreshDatabase;

    // ── index ──────────────────────────────────────────────────────────────

    public function test_teachers_index_returns_paginated(): void
    {
        Teacher::create(['name' => 'Alice']);
        Teacher::create(['name' => 'Bob']);

        $this->getJson('/api/teachers')
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Преподаватели получены'])
            ->assertJsonStructure(['data' => ['data', 'total', 'per_page', 'current_page']]);
    }

    public function test_teachers_index_returns_empty_paginator(): void
    {
        $response = $this->getJson('/api/teachers')->assertStatus(200);

        $this->assertEquals(0, $response->json('data.total'));
    }

    // ── store ──────────────────────────────────────────────────────────────

    public function test_teachers_store_success_as_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Admin]));

        $this->postJson('/api/teachers', ['name' => 'New Teacher'])
            ->assertStatus(201)
            ->assertJson(['success' => true, 'message' => 'Преподаватель создан'])
            ->assertJsonPath('data.name', 'New Teacher');

        $this->assertDatabaseHas('teachers', ['name' => 'New Teacher']);
    }

    public function test_teachers_store_with_disciplines(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Admin]));
        $discipline = Discipline::create(['name' => 'Math']);

        $response = $this->postJson('/api/teachers', [
            'name' => 'Math Teacher',
            'discipline_ids' => [$discipline->id],
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('teacher_disciplines', [
            'discipline_id' => $discipline->id,
        ]);
    }

    public function test_teachers_store_requires_name(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Admin]));

        $this->postJson('/api/teachers', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_teachers_store_requires_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Employee]));

        $this->postJson('/api/teachers', ['name' => 'X'])->assertStatus(403);
    }

    public function test_teachers_store_requires_auth(): void
    {
        $this->postJson('/api/teachers', ['name' => 'X'])->assertStatus(401);
    }

    // ── show ───────────────────────────────────────────────────────────────

    public function test_teachers_show_returns_teacher(): void
    {
        $teacher = Teacher::create(['name' => 'Jane Doe']);

        $this->getJson('/api/teachers/' . $teacher->id)
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Преподаватель получен'])
            ->assertJsonPath('data.id', $teacher->id)
            ->assertJsonPath('data.name', 'Jane Doe');
    }

    public function test_teachers_show_returns_404_when_not_found(): void
    {
        $this->getJson('/api/teachers/999')->assertStatus(404);
    }

    // ── update ─────────────────────────────────────────────────────────────

    public function test_teachers_update_success_as_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Admin]));
        $teacher = Teacher::create(['name' => 'Old Name']);

        $this->putJson('/api/teachers/' . $teacher->id, ['name' => 'New Name'])
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Преподаватель обновлён'])
            ->assertJsonPath('data.name', 'New Name');

        $this->assertDatabaseHas('teachers', ['id' => $teacher->id, 'name' => 'New Name']);
    }

    public function test_teachers_update_syncs_disciplines(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Admin]));
        $teacher = Teacher::create(['name' => 'Teacher']);
        $discipline = Discipline::create(['name' => 'Physics']);

        $this->putJson('/api/teachers/' . $teacher->id, [
            'name' => 'Teacher',
            'discipline_ids' => [$discipline->id],
        ])->assertStatus(200);

        $this->assertDatabaseHas('teacher_disciplines', [
            'teacher_id' => $teacher->id,
            'discipline_id' => $discipline->id,
        ]);
    }

    public function test_teachers_update_requires_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Employee]));
        $teacher = Teacher::create(['name' => 'Teacher']);

        $this->putJson('/api/teachers/' . $teacher->id, ['name' => 'X'])->assertStatus(403);
    }

    public function test_teachers_update_requires_auth(): void
    {
        $teacher = Teacher::create(['name' => 'Teacher']);

        $this->putJson('/api/teachers/' . $teacher->id, ['name' => 'X'])->assertStatus(401);
    }

    // ── destroy ────────────────────────────────────────────────────────────

    public function test_teachers_destroy_success_as_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Admin]));
        $teacher = Teacher::create(['name' => 'Delete Me']);

        $this->deleteJson('/api/teachers/' . $teacher->id)
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Преподаватель удалён']);

        $this->assertDatabaseMissing('teachers', ['id' => $teacher->id]);
    }

    public function test_teachers_destroy_requires_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Employee]));
        $teacher = Teacher::create(['name' => 'Teacher']);

        $this->deleteJson('/api/teachers/' . $teacher->id)->assertStatus(403);
    }

    public function test_teachers_destroy_requires_auth(): void
    {
        $teacher = Teacher::create(['name' => 'Teacher']);

        $this->deleteJson('/api/teachers/' . $teacher->id)->assertStatus(401);
    }

    // ── search ─────────────────────────────────────────────────────────────

    public function test_teachers_search_validation_errors_when_missing_discipline_id(): void
    {
        $this->getJson('/api/teachers/search')->assertStatus(422);
    }

    public function test_teachers_search_returns_404_when_no_results(): void
    {
        $discipline = Discipline::create(['name' => 'Math']);

        $this->getJson('/api/teachers/search?discipline_id=' . $discipline->id)
            ->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'Учителя не найдены', 'status' => 404]);
    }

    public function test_teachers_search_returns_results(): void
    {
        $discipline = Discipline::create(['name' => 'Math']);
        $teacher = Teacher::create(['name' => 'Math Expert']);
        $teacher->disciplines()->attach($discipline->id);

        $this->getJson('/api/teachers/search?discipline_id=' . $discipline->id)
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Учителя получены'])
            ->assertJsonCount(1, 'data');
    }
}
