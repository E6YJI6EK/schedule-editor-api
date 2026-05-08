<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Building;
use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassRoomsRoutesTest extends TestCase
{
    use RefreshDatabase;

    private Building $building;

    protected function setUp(): void
    {
        parent::setUp();
        $this->building = Building::create(['name' => 'Main', 'short_name' => 'M']);
    }

    // ── index ──────────────────────────────────────────────────────────────

    public function test_class_rooms_index_returns_empty(): void
    {
        $this->getJson('/api/class-rooms')
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Аудитории получены', 'data' => []]);
    }

    public function test_class_rooms_index_returns_all(): void
    {
        ClassRoom::create(['number' => '101', 'building_id' => $this->building->id]);
        ClassRoom::create(['number' => '102', 'building_id' => $this->building->id]);

        $this->getJson('/api/class-rooms')
            ->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(2, 'data');
    }

    // ── store ──────────────────────────────────────────────────────────────

    public function test_class_rooms_store_success_as_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Admin]));

        $this->postJson('/api/class-rooms', [
            'number' => '303',
            'building_id' => $this->building->id,
        ])
            ->assertStatus(201)
            ->assertJson(['success' => true, 'message' => 'Аудитория создана'])
            ->assertJsonPath('data.number', '303');

        $this->assertDatabaseHas('class_rooms', ['number' => '303']);
    }

    public function test_class_rooms_store_requires_number(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Admin]));

        $this->postJson('/api/class-rooms', ['building_id' => $this->building->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['number']);
    }

    public function test_class_rooms_store_requires_existing_building(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Admin]));

        $this->postJson('/api/class-rooms', ['number' => '101', 'building_id' => 999])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['building_id']);
    }

    public function test_class_rooms_store_requires_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Employee]));

        $this->postJson('/api/class-rooms', ['number' => '101', 'building_id' => $this->building->id])
            ->assertStatus(403);
    }

    public function test_class_rooms_store_requires_auth(): void
    {
        $this->postJson('/api/class-rooms', ['number' => '101', 'building_id' => $this->building->id])
            ->assertStatus(401);
    }

    // ── show ───────────────────────────────────────────────────────────────

    public function test_class_rooms_show_returns_class_room(): void
    {
        $classRoom = ClassRoom::create(['number' => '101', 'building_id' => $this->building->id]);

        $this->getJson('/api/class-rooms/' . $classRoom->id)
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Аудитория получена'])
            ->assertJsonPath('data.id', $classRoom->id)
            ->assertJsonPath('data.number', '101');
    }

    public function test_class_rooms_show_returns_404_when_not_found(): void
    {
        $this->getJson('/api/class-rooms/999')->assertStatus(404);
    }

    // ── update ─────────────────────────────────────────────────────────────

    public function test_class_rooms_update_success_as_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Admin]));
        $classRoom = ClassRoom::create(['number' => '100', 'building_id' => $this->building->id]);

        $this->putJson('/api/class-rooms/' . $classRoom->id, ['number' => '999', 'building_id' => $this->building->id])
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Аудитория обновлена'])
            ->assertJsonPath('data.number', '999');

        $this->assertDatabaseHas('class_rooms', ['id' => $classRoom->id, 'number' => '999']);
    }

    public function test_class_rooms_update_requires_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Employee]));
        $classRoom = ClassRoom::create(['number' => '100', 'building_id' => $this->building->id]);

        $this->putJson('/api/class-rooms/' . $classRoom->id, ['number' => '999'])->assertStatus(403);
    }

    public function test_class_rooms_update_requires_auth(): void
    {
        $classRoom = ClassRoom::create(['number' => '100', 'building_id' => $this->building->id]);

        $this->putJson('/api/class-rooms/' . $classRoom->id, ['number' => '999'])->assertStatus(401);
    }

    // ── destroy ────────────────────────────────────────────────────────────

    public function test_class_rooms_destroy_success_as_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Admin]));
        $classRoom = ClassRoom::create(['number' => '101', 'building_id' => $this->building->id]);

        $this->deleteJson('/api/class-rooms/' . $classRoom->id)
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Аудитория удалена']);

        $this->assertDatabaseMissing('class_rooms', ['id' => $classRoom->id]);
    }

    public function test_class_rooms_destroy_requires_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Employee]));
        $classRoom = ClassRoom::create(['number' => '101', 'building_id' => $this->building->id]);

        $this->deleteJson('/api/class-rooms/' . $classRoom->id)->assertStatus(403);
    }

    public function test_class_rooms_destroy_requires_auth(): void
    {
        $classRoom = ClassRoom::create(['number' => '101', 'building_id' => $this->building->id]);

        $this->deleteJson('/api/class-rooms/' . $classRoom->id)->assertStatus(401);
    }

    // ── search ─────────────────────────────────────────────────────────────

    public function test_class_rooms_search_validation_errors_when_missing_building_id(): void
    {
        $this->getJson('/api/class-rooms/search')->assertStatus(422);
    }

    public function test_class_rooms_search_returns_404_when_no_results(): void
    {
        $this->getJson('/api/class-rooms/search?building_id=' . $this->building->id)
            ->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'Аудитории не найдены', 'status' => 404]);
    }

    public function test_class_rooms_search_returns_results(): void
    {
        ClassRoom::create(['number' => '101', 'building_id' => $this->building->id]);

        $this->getJson('/api/class-rooms/search?building_id=' . $this->building->id . '&number=101')
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Аудитории получены'])
            ->assertJsonCount(1, 'data');
    }
}
