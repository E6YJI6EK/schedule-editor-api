<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Discipline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisciplinesRoutesTest extends TestCase
{
    use RefreshDatabase;

    // ── index ──────────────────────────────────────────────────────────────

    public function test_disciplines_index_returns_empty(): void
    {
        $this->getJson('/api/disciplines')
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Дисциплины получены', 'data' => []]);
    }

    public function test_disciplines_index_returns_all(): void
    {
        Discipline::create(['name' => 'Math']);
        Discipline::create(['name' => 'Physics']);

        $this->getJson('/api/disciplines')
            ->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(2, 'data');
    }

    // ── store ──────────────────────────────────────────────────────────────

    public function test_disciplines_store_success_as_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Admin]));

        $this->postJson('/api/disciplines', ['name' => 'Chemistry'])
            ->assertStatus(201)
            ->assertJson(['success' => true, 'message' => 'Дисциплина создана'])
            ->assertJsonPath('data.name', 'Chemistry');

        $this->assertDatabaseHas('disciplines', ['name' => 'Chemistry']);
    }

    public function test_disciplines_store_requires_name(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Admin]));

        $this->postJson('/api/disciplines', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_disciplines_store_requires_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Employee]));

        $this->postJson('/api/disciplines', ['name' => 'Chemistry'])->assertStatus(403);
    }

    public function test_disciplines_store_requires_auth(): void
    {
        $this->postJson('/api/disciplines', ['name' => 'Chemistry'])->assertStatus(401);
    }

    // ── show ───────────────────────────────────────────────────────────────

    public function test_disciplines_show_returns_discipline(): void
    {
        $discipline = Discipline::create(['name' => 'Math']);

        $this->getJson('/api/disciplines/' . $discipline->id)
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Дисциплина получена'])
            ->assertJsonPath('data.id', $discipline->id)
            ->assertJsonPath('data.name', 'Math');
    }

    public function test_disciplines_show_returns_404_when_not_found(): void
    {
        $this->getJson('/api/disciplines/999')->assertStatus(404);
    }

    // ── update ─────────────────────────────────────────────────────────────

    public function test_disciplines_update_success_as_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Admin]));
        $discipline = Discipline::create(['name' => 'Old Name']);

        $this->putJson('/api/disciplines/' . $discipline->id, ['name' => 'New Name'])
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Дисциплина обновлена'])
            ->assertJsonPath('data.name', 'New Name');

        $this->assertDatabaseHas('disciplines', ['id' => $discipline->id, 'name' => 'New Name']);
    }

    public function test_disciplines_update_requires_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Employee]));
        $discipline = Discipline::create(['name' => 'Math']);

        $this->putJson('/api/disciplines/' . $discipline->id, ['name' => 'X'])->assertStatus(403);
    }

    public function test_disciplines_update_requires_auth(): void
    {
        $discipline = Discipline::create(['name' => 'Math']);

        $this->putJson('/api/disciplines/' . $discipline->id, ['name' => 'X'])->assertStatus(401);
    }

    // ── destroy ────────────────────────────────────────────────────────────

    public function test_disciplines_destroy_success_as_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Admin]));
        $discipline = Discipline::create(['name' => 'Delete Me']);

        $this->deleteJson('/api/disciplines/' . $discipline->id)
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Дисциплина удалена']);

        $this->assertDatabaseMissing('disciplines', ['id' => $discipline->id]);
    }

    public function test_disciplines_destroy_requires_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Employee]));
        $discipline = Discipline::create(['name' => 'Math']);

        $this->deleteJson('/api/disciplines/' . $discipline->id)->assertStatus(403);
    }

    public function test_disciplines_destroy_requires_auth(): void
    {
        $discipline = Discipline::create(['name' => 'Math']);

        $this->deleteJson('/api/disciplines/' . $discipline->id)->assertStatus(401);
    }

    // ── search ─────────────────────────────────────────────────────────────

    public function test_disciplines_search_returns_404_when_no_results(): void
    {
        $this->getJson('/api/disciplines/search')
            ->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'Дисциплины не найдены', 'status' => 404]);
    }

    public function test_disciplines_search_returns_results(): void
    {
        Discipline::create(['name' => 'Advanced Math']);

        $this->getJson('/api/disciplines/search?name=Math')
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Дисциплины получены'])
            ->assertJsonCount(1, 'data');
    }
}
