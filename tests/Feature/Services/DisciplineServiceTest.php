<?php

namespace Tests\Feature\Services;

use App\Models\Discipline;
use App\Models\Teacher;
use App\Services\DisciplineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisciplineServiceTest extends TestCase
{
    use RefreshDatabase;

    private DisciplineService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DisciplineService();
    }

    public function test_all_returns_empty_collection_when_none(): void
    {
        $result = $this->service->all();

        $this->assertCount(0, $result);
    }

    public function test_all_returns_disciplines_with_teachers(): void
    {
        $discipline = Discipline::create(['name' => 'Math']);
        $teacher = Teacher::create(['name' => 'John Doe']);
        $teacher->disciplines()->attach($discipline->id);

        $result = $this->service->all();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->relationLoaded('teachers'));
        $this->assertCount(1, $result->first()->teachers);
    }

    public function test_find_returns_discipline_with_teachers(): void
    {
        $discipline = Discipline::create(['name' => 'Physics']);

        $result = $this->service->find($discipline->id);

        $this->assertEquals($discipline->id, $result->id);
        $this->assertEquals('Physics', $result->name);
        $this->assertTrue($result->relationLoaded('teachers'));
    }

    public function test_find_throws_when_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->find(999);
    }

    public function test_search_by_name_returns_matching(): void
    {
        Discipline::create(['name' => 'Mathematics']);
        Discipline::create(['name' => 'Physics']);

        $result = $this->service->search(['name' => 'Math']);

        $this->assertCount(1, $result);
        $this->assertEquals('Mathematics', $result->first()->name);
    }

    public function test_search_by_name_partial_match(): void
    {
        Discipline::create(['name' => 'Advanced Math']);
        Discipline::create(['name' => 'Basic Math']);
        Discipline::create(['name' => 'Physics']);

        $result = $this->service->search(['name' => 'Math']);

        $this->assertCount(2, $result);
    }

    public function test_search_without_filters_returns_up_to_10(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            Discipline::create(['name' => "Discipline $i"]);
        }

        $result = $this->service->search([]);

        $this->assertCount(10, $result);
    }

    public function test_search_returns_empty_when_no_match(): void
    {
        Discipline::create(['name' => 'Math']);

        $result = $this->service->search(['name' => 'Biology']);

        $this->assertCount(0, $result);
    }

    public function test_store_creates_discipline(): void
    {
        $result = $this->service->store(['name' => 'Chemistry']);

        $this->assertInstanceOf(Discipline::class, $result);
        $this->assertEquals('Chemistry', $result->name);
        $this->assertDatabaseHas('disciplines', ['name' => 'Chemistry']);
    }

    public function test_update_changes_discipline_name(): void
    {
        $discipline = Discipline::create(['name' => 'Old Name']);

        $result = $this->service->update($discipline, ['name' => 'New Name']);

        $this->assertEquals('New Name', $result->name);
        $this->assertDatabaseHas('disciplines', ['id' => $discipline->id, 'name' => 'New Name']);
    }

    public function test_delete_removes_discipline(): void
    {
        $discipline = Discipline::create(['name' => 'To Delete']);

        $this->service->delete($discipline);

        $this->assertDatabaseMissing('disciplines', ['id' => $discipline->id]);
    }
}
