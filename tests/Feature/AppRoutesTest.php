<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_route_returns_alive_response(): void
    {
        $response = $this->get('/api');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Success',
                'data' => 'I AM ALIVE!!!',
                'status' => 200,
            ]);
    }
}


