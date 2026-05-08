<?php

namespace Tests\Feature\Services;

use App\Enums\Course;
use App\Enums\Day;
use App\Enums\EducationForm;
use App\Enums\WeekType;
use App\Models\Building;
use App\Models\ClassRoom;
use App\Models\DayPartition;
use App\Models\Discipline;
use App\Models\Group;
use App\Models\Institute;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Models\TimeSlot;
use App\Services\LessonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonServiceTest extends TestCase
{
    use RefreshDatabase;

    private LessonService $service;

    private Teacher $teacher;
    private ClassRoom $classRoom;
    private TimeSlot $timeSlotUpper;
    private TimeSlot $timeSlotLower;
    private Discipline $discipline;
    private Group $group;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LessonService();

        $building = Building::create(['name' => 'Main', 'short_name' => 'M']);
        $this->classRoom = ClassRoom::create(['number' => '101', 'building_id' => $building->id]);
        $dayPartition = DayPartition::create(['start_time' => '08:30', 'end_time' => '10:00']);
        $this->timeSlotUpper = TimeSlot::create([
            'week_type' => WeekType::Upper,
            'day' => Day::Monday,
            'day_partition_id' => $dayPartition->id,
        ]);
        $this->timeSlotLower = TimeSlot::create([
            'week_type' => WeekType::Lower,
            'day' => Day::Monday,
            'day_partition_id' => $dayPartition->id,
        ]);
        $this->discipline = Discipline::create(['name' => 'Math']);
        $this->teacher = Teacher::create(['name' => 'John Doe']);
        $institute = Institute::create(['name' => 'Tech']);
        $this->group = Group::create([
            'name' => 'A-01',
            'course' => Course::First,
            'education_form' => EducationForm::Intramural,
            'institute_id' => $institute->id,
        ]);
    }

    private function lessonPayload(array $override = []): array
    {
        return array_merge([
            'teacher_id' => $this->teacher->id,
            'class_room_id' => $this->classRoom->id,
            'time_slot_id' => $this->timeSlotUpper->id,
            'discipline_id' => $this->discipline->id,
            'group_id' => $this->group->id,
        ], $override);
    }

    // --- all ---

    public function test_all_returns_empty_collection(): void
    {
        $result = $this->service->all();

        $this->assertCount(0, $result);
    }

    public function test_all_returns_lessons_with_relations(): void
    {
        Lesson::create($this->lessonPayload());

        $result = $this->service->all();

        $this->assertCount(1, $result);
        $lesson = $result->first();
        $this->assertTrue($lesson->relationLoaded('teacher'));
        $this->assertTrue($lesson->relationLoaded('classRoom'));
        $this->assertTrue($lesson->relationLoaded('timeSlot'));
        $this->assertTrue($lesson->relationLoaded('discipline'));
        $this->assertTrue($lesson->relationLoaded('group'));
    }

    // --- find ---

    public function test_find_returns_lesson_with_relations(): void
    {
        $lesson = Lesson::create($this->lessonPayload());

        $result = $this->service->find($lesson->id);

        $this->assertEquals($lesson->id, $result->id);
        $this->assertTrue($result->relationLoaded('teacher'));
        $this->assertTrue($result->relationLoaded('discipline'));
    }

    public function test_find_throws_when_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->find(999);
    }

    // --- create ---

    public function test_create_returns_lesson_on_success(): void
    {
        $result = $this->service->create($this->lessonPayload());

        $this->assertArrayHasKey('lesson', $result);
        $this->assertInstanceOf(Lesson::class, $result['lesson']);
        $this->assertDatabaseHas('lessons', $this->lessonPayload());
    }

    public function test_create_loads_relations_on_new_lesson(): void
    {
        $result = $this->service->create($this->lessonPayload());

        $lesson = $result['lesson'];
        $this->assertTrue($lesson->relationLoaded('teacher'));
        $this->assertTrue($lesson->relationLoaded('discipline'));
        $this->assertTrue($lesson->relationLoaded('group'));
    }

    public function test_create_returns_error_on_duplicate(): void
    {
        $payload = $this->lessonPayload();
        $this->service->create($payload);

        $result = $this->service->create($payload);

        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('duplicate', $result['error']);
    }

    public function test_create_allows_same_teacher_different_time_slot(): void
    {
        $this->service->create($this->lessonPayload(['time_slot_id' => $this->timeSlotUpper->id]));

        $result = $this->service->create($this->lessonPayload(['time_slot_id' => $this->timeSlotLower->id]));

        $this->assertArrayHasKey('lesson', $result);
    }

    // --- update ---

    public function test_update_changes_lesson_fields(): void
    {
        $lesson = Lesson::create($this->lessonPayload());
        $newDiscipline = Discipline::create(['name' => 'Physics']);

        $result = $this->service->update($lesson, ['discipline_id' => $newDiscipline->id]);

        $this->assertEquals($newDiscipline->id, $result->discipline_id);
        $this->assertDatabaseHas('lessons', ['id' => $lesson->id, 'discipline_id' => $newDiscipline->id]);
    }

    public function test_update_loads_relations(): void
    {
        $lesson = Lesson::create($this->lessonPayload());

        $result = $this->service->update($lesson, ['group_id' => $this->group->id]);

        $this->assertTrue($result->relationLoaded('teacher'));
        $this->assertTrue($result->relationLoaded('discipline'));
    }

    // --- delete ---

    public function test_delete_removes_lesson(): void
    {
        $lesson = Lesson::create($this->lessonPayload());

        $this->service->delete($lesson);

        $this->assertDatabaseMissing('lessons', ['id' => $lesson->id]);
    }

    // --- getSchedule ---

    public function test_get_schedule_returns_upper_week_lessons(): void
    {
        Lesson::create($this->lessonPayload(['time_slot_id' => $this->timeSlotUpper->id]));
        Lesson::create($this->lessonPayload(['time_slot_id' => $this->timeSlotLower->id]));

        $result = $this->service->getSchedule([$this->group->id], true);

        $this->assertCount(1, $result);
        $this->assertEquals($this->timeSlotUpper->id, $result->first()->time_slot_id);
    }

    public function test_get_schedule_returns_lower_week_lessons(): void
    {
        Lesson::create($this->lessonPayload(['time_slot_id' => $this->timeSlotUpper->id]));
        Lesson::create($this->lessonPayload(['time_slot_id' => $this->timeSlotLower->id]));

        $result = $this->service->getSchedule([$this->group->id], false);

        $this->assertCount(1, $result);
        $this->assertEquals($this->timeSlotLower->id, $result->first()->time_slot_id);
    }

    public function test_get_schedule_filters_by_group_ids(): void
    {
        $institute = Institute::create(['name' => 'Other']);
        $otherGroup = Group::create([
            'name' => 'B-02',
            'course' => Course::Second,
            'education_form' => EducationForm::Intramural,
            'institute_id' => $institute->id,
        ]);

        Lesson::create($this->lessonPayload());
        Lesson::create($this->lessonPayload(['group_id' => $otherGroup->id]));

        $result = $this->service->getSchedule([$this->group->id], true);

        $this->assertCount(1, $result);
        $this->assertEquals($this->group->id, $result->first()->group_id);
    }

    public function test_get_schedule_for_multiple_groups(): void
    {
        $institute = Institute::create(['name' => 'Other']);
        $otherGroup = Group::create([
            'name' => 'B-02',
            'course' => Course::Second,
            'education_form' => EducationForm::Intramural,
            'institute_id' => $institute->id,
        ]);

        Lesson::create($this->lessonPayload());
        Lesson::create($this->lessonPayload(['group_id' => $otherGroup->id]));

        $result = $this->service->getSchedule([$this->group->id, $otherGroup->id], true);

        $this->assertCount(2, $result);
    }

    public function test_get_schedule_loads_relations(): void
    {
        Lesson::create($this->lessonPayload());

        $result = $this->service->getSchedule([$this->group->id], true);

        $lesson = $result->first();
        $this->assertTrue($lesson->relationLoaded('teacher'));
        $this->assertTrue($lesson->relationLoaded('classRoom'));
        $this->assertTrue($lesson->relationLoaded('timeSlot'));
        $this->assertTrue($lesson->relationLoaded('discipline'));
        $this->assertTrue($lesson->relationLoaded('group'));
    }

    public function test_get_schedule_returns_empty_when_no_match(): void
    {
        $result = $this->service->getSchedule([$this->group->id], true);

        $this->assertCount(0, $result);
    }

    // --- findTimeSlot ---

    public function test_find_time_slot_returns_matching_slot(): void
    {
        $result = $this->service->findTimeSlot([
            'week_type' => WeekType::Upper,
            'day' => Day::Monday,
            'day_partition_id' => $this->timeSlotUpper->day_partition_id,
        ]);

        $this->assertNotNull($result);
        $this->assertEquals($this->timeSlotUpper->id, $result->id);
    }

    public function test_find_time_slot_returns_null_when_not_found(): void
    {
        $result = $this->service->findTimeSlot([
            'week_type' => WeekType::Upper,
            'day' => Day::Tuesday,
            'day_partition_id' => $this->timeSlotUpper->day_partition_id,
        ]);

        $this->assertNull($result);
    }

    public function test_find_time_slot_distinguishes_week_types(): void
    {
        $lower = $this->service->findTimeSlot([
            'week_type' => WeekType::Lower,
            'day' => Day::Monday,
            'day_partition_id' => $this->timeSlotLower->day_partition_id,
        ]);

        $this->assertNotNull($lower);
        $this->assertEquals($this->timeSlotLower->id, $lower->id);
    }
}
