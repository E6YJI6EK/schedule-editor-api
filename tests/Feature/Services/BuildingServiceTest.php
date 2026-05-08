<?php

namespace Tests\Feature\Services;

use App\Models\Building;
use App\Models\ClassRoom;
use App\Services\BuildingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuildingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BuildingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BuildingService();
    }

    public function test_all_returns_empty_collection_when_no_buildings(): void
    {
        $result = $this->service->all();

        $this->assertCount(0, $result);
    }

    public function test_all_returns_buildings_with_class_rooms(): void
    {
        $building = Building::create(['name' => 'Main', 'short_name' => 'M']);
        ClassRoom::create(['number' => '101', 'building_id' => $building->id]);
        ClassRoom::create(['number' => '102', 'building_id' => $building->id]);

        $result = $this->service->all();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->relationLoaded('classRooms'));
        $this->assertCount(2, $result->first()->classRooms);
    }

    public function test_find_returns_building_by_id(): void
    {
        $building = Building::create(['name' => 'Main', 'short_name' => 'M']);

        $result = $this->service->find($building->id);

        $this->assertEquals($building->id, $result->id);
        $this->assertEquals('Main', $result->name);
        $this->assertTrue($result->relationLoaded('classRooms'));
    }

    public function test_find_throws_when_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->find(999);
    }

    public function test_search_by_name_filters_correctly(): void
    {
        Building::create(['name' => 'Main Building', 'short_name' => 'M']);
        Building::create(['name' => 'Annex', 'short_name' => 'A']);

        $result = $this->service->search(['name' => 'Main']);

        $this->assertCount(1, $result);
        $this->assertEquals('Main Building', $result->first()->name);
    }

    public function test_search_by_name_partial_match(): void
    {
        Building::create(['name' => 'Main Building', 'short_name' => 'M']);
        Building::create(['name' => 'Main Annex', 'short_name' => 'A']);
        Building::create(['name' => 'Library', 'short_name' => 'L']);

        $result = $this->service->search(['name' => 'Main']);

        $this->assertCount(2, $result);
    }

    public function test_search_without_filters_returns_up_to_10(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            Building::create(['name' => "Building $i", 'short_name' => "B$i"]);
        }

        $result = $this->service->search([]);

        $this->assertCount(10, $result);
    }

    public function test_search_returns_empty_when_no_match(): void
    {
        Building::create(['name' => 'Main', 'short_name' => 'M']);

        $result = $this->service->search(['name' => 'Nonexistent']);

        $this->assertCount(0, $result);
    }

    public function test_store_creates_building(): void
    {
        $result = $this->service->store(['name' => 'New Building', 'short_name' => 'NB']);

        $this->assertInstanceOf(Building::class, $result);
        $this->assertEquals('New Building', $result->name);
        $this->assertEquals('NB', $result->short_name);
        $this->assertDatabaseHas('buildings', ['name' => 'New Building', 'short_name' => 'NB']);
    }

    public function test_update_changes_building_fields(): void
    {
        $building = Building::create(['name' => 'Old Name', 'short_name' => 'ON']);

        $result = $this->service->update($building, ['name' => 'New Name', 'short_name' => 'NN']);

        $this->assertEquals('New Name', $result->name);
        $this->assertEquals('NN', $result->short_name);
        $this->assertDatabaseHas('buildings', ['id' => $building->id, 'name' => 'New Name']);
    }

    public function test_delete_removes_building(): void
    {
        $building = Building::create(['name' => 'To Delete', 'short_name' => 'TD']);

        $this->service->delete($building);

        $this->assertDatabaseMissing('buildings', ['id' => $building->id]);
    }
}
