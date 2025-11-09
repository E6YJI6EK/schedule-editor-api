<?php

namespace Tests\Feature;

use App\Models\Building;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassRoomsRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_class_rooms_search_validation_errors_when_missing_building_id(): void
    {
        $response = $this->getJson('/api/class-rooms/search');
        $response->assertStatus(422);
    }

    public function test_class_rooms_search_returns_404_when_no_results(): void
    {
        $building = Building::create(['name' => 'Main', 'short_name' => 'M']);

        $response = $this->getJson('/api/class-rooms/search?building_id=' . $building->id);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Аудитории не найдены',
                'status' => 404,
            ]);
    }
}


