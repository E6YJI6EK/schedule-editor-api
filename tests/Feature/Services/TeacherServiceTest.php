<?php

namespace Tests\Feature\Services;

use App\Models\Discipline;
use App\Models\Teacher;
use App\Services\TeacherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherServiceTest extends TestCase
{
    use RefreshDatabase;

    private TeacherService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TeacherService();
    }

    public function test_paginate_latest_returns_paginator(): void
    {
        Teacher::create(['name' => 'Alice']);
        Teacher::create(['name' => 'Bob']);

        $result = $this->service->paginateLatest(10);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
        $this->assertEquals(2, $result->total());
        $this->assertTrue($result->first()->relationLoaded('disciplines'));
    }

    public function test_paginate_latest_respects_per_page(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            Teacher::create(['name' => "Teacher $i"]);
        }

        $result = $this->service->paginateLatest(5);

        $this->assertCount(5, $result->items());
        $this->assertEquals(15, $result->total());
    }

    public function test_paginate_latest_orders_by_latest(): void
    {
        $first = new Teacher(['name' => 'First']);
        $first->created_at = now()->subMinute();
        $first->save();

        $second = Teacher::create(['name' => 'Second']);

        $result = $this->service->paginateLatest(10);

        $this->assertEquals($second->id, $result->first()->id);
    }

    public function test_find_returns_teacher_with_disciplines(): void
    {
        $teacher = Teacher::create(['name' => 'Jane Doe']);
        $discipline = Discipline::create(['name' => 'Physics']);
        $teacher->disciplines()->attach($discipline->id);

        $result = $this->service->find($teacher->id);

        $this->assertEquals($teacher->id, $result->id);
        $this->assertTrue($result->relationLoaded('disciplines'));
        $this->assertCount(1, $result->disciplines);
    }

    public function test_find_throws_when_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->find(999);
    }

    public function test_search_by_discipline_id(): void
    {
        $math = Discipline::create(['name' => 'Math']);
        $physics = Discipline::create(['name' => 'Physics']);
        $mathTeacher = Teacher::create(['name' => 'Math Teacher']);
        $physicsTeacher = Teacher::create(['name' => 'Physics Teacher']);
        $mathTeacher->disciplines()->attach($math->id);
        $physicsTeacher->disciplines()->attach($physics->id);

        $result = $this->service->search(['discipline_id' => $math->id]);

        $this->assertCount(1, $result);
        $this->assertEquals('Math Teacher', $result->first()->name);
    }

    public function test_search_by_name(): void
    {
        Teacher::create(['name' => 'John Smith']);
        Teacher::create(['name' => 'Jane Doe']);

        $result = $this->service->search(['name' => 'John']);

        $this->assertCount(1, $result);
        $this->assertEquals('John Smith', $result->first()->name);
    }

    public function test_search_by_discipline_and_name(): void
    {
        $math = Discipline::create(['name' => 'Math']);
        $t1 = Teacher::create(['name' => 'John Math']);
        $t2 = Teacher::create(['name' => 'Jane Math']);
        $t1->disciplines()->attach($math->id);
        $t2->disciplines()->attach($math->id);

        $result = $this->service->search(['discipline_id' => $math->id, 'name' => 'John']);

        $this->assertCount(1, $result);
        $this->assertEquals('John Math', $result->first()->name);
    }

    public function test_search_without_filters_returns_up_to_10(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            Teacher::create(['name' => "Teacher $i"]);
        }

        $result = $this->service->search([]);

        $this->assertCount(10, $result);
    }

    public function test_search_returns_empty_when_no_match(): void
    {
        Teacher::create(['name' => 'Alice']);

        $result = $this->service->search(['name' => 'Nonexistent']);

        $this->assertCount(0, $result);
    }

    public function test_store_creates_teacher_without_disciplines(): void
    {
        $result = $this->service->store(['name' => 'New Teacher']);

        $this->assertInstanceOf(Teacher::class, $result);
        $this->assertEquals('New Teacher', $result->name);
        $this->assertDatabaseHas('teachers', ['name' => 'New Teacher']);
        $this->assertCount(0, $result->disciplines);
    }

    public function test_store_creates_teacher_with_disciplines(): void
    {
        $math = Discipline::create(['name' => 'Math']);
        $physics = Discipline::create(['name' => 'Physics']);

        $result = $this->service->store([
            'name' => 'Multi Teacher',
            'discipline_ids' => [$math->id, $physics->id],
        ]);

        $this->assertEquals('Multi Teacher', $result->name);
        $this->assertTrue($result->relationLoaded('disciplines'));
        $this->assertCount(2, $result->disciplines);
        $this->assertDatabaseHas('teacher_disciplines', ['teacher_id' => $result->id, 'discipline_id' => $math->id]);
        $this->assertDatabaseHas('teacher_disciplines', ['teacher_id' => $result->id, 'discipline_id' => $physics->id]);
    }

    public function test_update_changes_name(): void
    {
        $teacher = Teacher::create(['name' => 'Old Name']);

        $result = $this->service->update($teacher, ['name' => 'New Name']);

        $this->assertEquals('New Name', $result->name);
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id, 'name' => 'New Name']);
    }

    public function test_update_keeps_name_when_not_provided(): void
    {
        $discipline = Discipline::create(['name' => 'Math']);
        $teacher = Teacher::create(['name' => 'Stable Name']);

        $result = $this->service->update($teacher, ['discipline_ids' => [$discipline->id]]);

        $this->assertEquals('Stable Name', $result->name);
    }

    public function test_update_syncs_disciplines(): void
    {
        $math = Discipline::create(['name' => 'Math']);
        $physics = Discipline::create(['name' => 'Physics']);
        $teacher = Teacher::create(['name' => 'Teacher']);
        $teacher->disciplines()->attach($math->id);

        $result = $this->service->update($teacher, ['discipline_ids' => [$physics->id]]);

        $this->assertCount(1, $result->disciplines);
        $this->assertEquals($physics->id, $result->disciplines->first()->id);
        $this->assertDatabaseMissing('teacher_disciplines', ['teacher_id' => $teacher->id, 'discipline_id' => $math->id]);
    }

    public function test_update_does_not_sync_when_discipline_ids_absent(): void
    {
        $math = Discipline::create(['name' => 'Math']);
        $teacher = Teacher::create(['name' => 'Teacher']);
        $teacher->disciplines()->attach($math->id);

        $result = $this->service->update($teacher, ['name' => 'Renamed']);

        $this->assertCount(1, $result->disciplines);
    }

    public function test_delete_removes_teacher(): void
    {
        $teacher = Teacher::create(['name' => 'To Delete']);

        $this->service->delete($teacher);

        $this->assertDatabaseMissing('teachers', ['id' => $teacher->id]);
    }
}
