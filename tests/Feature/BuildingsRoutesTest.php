<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Building;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuildingsRoutesTest extends TestCase
{
    use RefreshDatabase;

    // ── index ──────────────────────────────────────────────────────────────

    public function test_buildings_index_returns_empty(): void
    {
        $this->getJson('/api/buildings')
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Корпуса получены', 'data' => []]);
    }

    public function test_buildings_index_returns_all(): void
    {
        Building::create(['name' => 'Main', 'short_name' => 'M']);
        Building::create(['name' => 'Annex', 'short_name' => 'A']);

        $this->getJson('/api/buildings')
            ->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(2, 'data');
    }

    // ── store ──────────────────────────────────────────────────────────────

    public function test_buildings_store_success_as_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Admin]));

        $this->postJson('/api/buildings', ['name' => 'New Building', 'short_name' => 'NB'])
            ->assertStatus(201)
            ->assertJson(['success' => true, 'message' => 'Корпус создан'])
            ->assertJsonPath('data.name', 'New Building');

        $this->assertDatabaseHas('buildings', ['name' => 'New Building']);
    }

    public function test_buildings_store_requires_name(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Admin]));

        $this->postJson('/api/buildings', ['short_name' => 'NB'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_buildings_store_requires_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Employee]));

        $this->postJson('/api/buildings', ['name' => 'B', 'short_name' => 'B'])
            ->assertStatus(403);
    }

    public function test_buildings_store_requires_auth(): void
    {
        $this->postJson('/api/buildings', ['name' => 'B', 'short_name' => 'B'])
            ->assertStatus(401);
    }

    // ── show ───────────────────────────────────────────────────────────────

    public function test_buildings_show_returns_building(): void
    {
        $building = Building::create(['name' => 'Main', 'short_name' => 'M']);

        $this->getJson('/api/buildings/' . $building->id)
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Корпус получен'])
            ->assertJsonPath('data.id', $building->id)
            ->assertJsonPath('data.name', 'Main');
    }

    public function test_buildings_show_returns_404_when_not_found(): void
    {
        $this->getJson('/api/buildings/999')->assertStatus(404);
    }

    // ── update ─────────────────────────────────────────────────────────────

    public function test_buildings_update_success_as_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Admin]));
        $building = Building::create(['name' => 'Old', 'short_name' => 'O']);

        $this->putJson('/api/buildings/' . $building->id, ['name' => 'New', 'short_name' => 'N'])
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Корпус обновлён'])
            ->assertJsonPath('data.name', 'New');

        $this->assertDatabaseHas('buildings', ['id' => $building->id, 'name' => 'New']);
    }

    public function test_buildings_update_requires_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Employee]));
        $building = Building::create(['name' => 'B', 'short_name' => 'B']);

        $this->putJson('/api/buildings/' . $building->id, ['name' => 'X', 'short_name' => 'X'])
            ->assertStatus(403);
    }

    public function test_buildings_update_requires_auth(): void
    {
        $building = Building::create(['name' => 'B', 'short_name' => 'B']);

        $this->putJson('/api/buildings/' . $building->id, ['name' => 'X', 'short_name' => 'X'])
            ->assertStatus(401);
    }

    // ── destroy ────────────────────────────────────────────────────────────

    public function test_buildings_destroy_success_as_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Admin]));
        $building = Building::create(['name' => 'Delete Me', 'short_name' => 'DM']);

        $this->deleteJson('/api/buildings/' . $building->id)
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Корпус удалён']);

        $this->assertDatabaseMissing('buildings', ['id' => $building->id]);
    }

    public function test_buildings_destroy_requires_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Employee]));
        $building = Building::create(['name' => 'B', 'short_name' => 'B']);

        $this->deleteJson('/api/buildings/' . $building->id)->assertStatus(403);
    }

    public function test_buildings_destroy_requires_auth(): void
    {
        $building = Building::create(['name' => 'B', 'short_name' => 'B']);

        $this->deleteJson('/api/buildings/' . $building->id)->assertStatus(401);
    }

    // ── search ─────────────────────────────────────────────────────────────

    public function test_buildings_search_returns_404_when_no_results(): void
    {
        $this->getJson('/api/buildings/search')
            ->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'Корпуса не найдены', 'status' => 404]);
    }

    public function test_buildings_search_returns_results(): void
    {
        Building::create(['name' => 'Science Block', 'short_name' => 'SB']);

        $this->getJson('/api/buildings/search?name=Science')
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Корпуса получены'])
            ->assertJsonCount(1, 'data');
    }
}
