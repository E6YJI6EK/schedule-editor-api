<?php

namespace Tests\Feature;

use App\Models\Discipline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeachersRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_teachers_search_validation_errors_when_missing_discipline_id(): void
    {
        $response = $this->getJson('/teachers/search');
        $response->assertStatus(422);
    }

    public function test_teachers_search_returns_404_when_no_results(): void
    {
        $discipline = Discipline::create(['name' => 'Math']);

        $response = $this->getJson('/teachers/search?discipline_id=' . $discipline->id);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Учителя не найдены',
                'status' => 404,
            ]);
    }
}


