<?php

namespace Tests\Feature\Services;

use App\Models\Building;
use App\Models\ClassRoom;
use App\Services\ClassRoomService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassRoomServiceTest extends TestCase
{
    use RefreshDatabase;

    private ClassRoomService $service;
    private Building $building;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ClassRoomService();
        $this->building = Building::create(['name' => 'Main', 'short_name' => 'M']);
    }

    public function test_all_returns_empty_collection_when_none(): void
    {
        $result = $this->service->all();

        $this->assertCount(0, $result);
    }

    public function test_all_returns_class_rooms_with_building(): void
    {
        ClassRoom::create(['number' => '101', 'building_id' => $this->building->id]);
        ClassRoom::create(['number' => '102', 'building_id' => $this->building->id]);

        $result = $this->service->all();

        $this->assertCount(2, $result);
        $this->assertTrue($result->first()->relationLoaded('building'));
        $this->assertEquals($this->building->id, $result->first()->building->id);
    }

    public function test_find_returns_class_room_with_building(): void
    {
        $classRoom = ClassRoom::create(['number' => '101', 'building_id' => $this->building->id]);

        $result = $this->service->find($classRoom->id);

        $this->assertEquals($classRoom->id, $result->id);
        $this->assertEquals('101', $result->number);
        $this->assertTrue($result->relationLoaded('building'));
    }

    public function test_find_throws_when_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->find(999);
    }

    public function test_search_by_building_id(): void
    {
        $other = Building::create(['name' => 'Other', 'short_name' => 'O']);
        ClassRoom::create(['number' => '101', 'building_id' => $this->building->id]);
        ClassRoom::create(['number' => '201', 'building_id' => $other->id]);

        $result = $this->service->search(['building_id' => $this->building->id]);

        $this->assertCount(1, $result);
        $this->assertEquals('101', $result->first()->number);
    }

    public function test_search_by_number(): void
    {
        ClassRoom::create(['number' => '101', 'building_id' => $this->building->id]);
        ClassRoom::create(['number' => '102', 'building_id' => $this->building->id]);
        ClassRoom::create(['number' => '201', 'building_id' => $this->building->id]);

        $result = $this->service->search(['number' => '10']);

        $this->assertCount(2, $result);
    }

    public function test_search_by_building_id_and_number(): void
    {
        $other = Building::create(['name' => 'Other', 'short_name' => 'O']);
        ClassRoom::create(['number' => '101', 'building_id' => $this->building->id]);
        ClassRoom::create(['number' => '101', 'building_id' => $other->id]);

        $result = $this->service->search(['building_id' => $this->building->id, 'number' => '101']);

        $this->assertCount(1, $result);
        $this->assertEquals($this->building->id, $result->first()->building_id);
    }

    public function test_search_without_filters_returns_up_to_10(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            ClassRoom::create(['number' => (string) $i, 'building_id' => $this->building->id]);
        }

        $result = $this->service->search([]);

        $this->assertCount(10, $result);
    }

    public function test_store_creates_class_room(): void
    {
        $result = $this->service->store(['number' => '303', 'building_id' => $this->building->id]);

        $this->assertInstanceOf(ClassRoom::class, $result);
        $this->assertEquals('303', $result->number);
        $this->assertDatabaseHas('class_rooms', ['number' => '303', 'building_id' => $this->building->id]);
    }

    public function test_update_changes_class_room_fields(): void
    {
        $classRoom = ClassRoom::create(['number' => '100', 'building_id' => $this->building->id]);
        $other = Building::create(['name' => 'Other', 'short_name' => 'O']);

        $result = $this->service->update($classRoom, ['number' => '999', 'building_id' => $other->id]);

        $this->assertEquals('999', $result->number);
        $this->assertEquals($other->id, $result->building_id);
        $this->assertTrue($result->relationLoaded('building'));
    }

    public function test_delete_removes_class_room(): void
    {
        $classRoom = ClassRoom::create(['number' => '101', 'building_id' => $this->building->id]);

        $this->service->delete($classRoom);

        $this->assertDatabaseMissing('class_rooms', ['id' => $classRoom->id]);
    }
}
