<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisciplinesRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_disciplines_search_returns_404_when_no_results(): void
    {
        $response = $this->getJson('/disciplines/search');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Дисциплины не найдены',
                'status' => 404,
            ]);
    }
}


