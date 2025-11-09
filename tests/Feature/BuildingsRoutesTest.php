<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuildingsRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_buildings_search_returns_404_when_no_results(): void
    {
        $response = $this->getJson('/api/buildings/search');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Корпуса не найдены',
                'status' => 404,
            ]);
    }
}


