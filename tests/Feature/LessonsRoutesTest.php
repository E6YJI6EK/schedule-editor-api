<?php

namespace Tests\Feature;

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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonsRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_lessons_create_requires_validation(): void
    {
        $response = $this->postJson('/api/lessons/create', []);
        $response->assertStatus(422);
    }

    public function test_lessons_create_success_and_duplicate_conflict(): void
    {
        $building = Building::create(['name' => 'Main', 'short_name' => 'M']);
        $classRoom = ClassRoom::create(['number' => '101', 'building_id' => $building->id]);
        $dayPartition = DayPartition::create(['start_time' => '08:30', 'end_time' => '10:00']);
        $timeSlot = TimeSlot::create([
            'week_type' => WeekType::Upper,
            'day' => Day::Monday,
            'day_partition_id' => $dayPartition->id,
        ]);
        $discipline = Discipline::create(['name' => 'Math']);
        $teacher = Teacher::create(['name' => 'John Doe']);
        $institute = Institute::create(['name' => 'Tech']);
        $group = Group::create([
            'name' => 'A-01',
            'course' => Course::First,
            'education_form' => EducationForm::Intramural,
            'institute_id' => $institute->id,
        ]);

        $payload = [
            'teacher_id' => $teacher->id,
            'class_room_id' => $classRoom->id,
            'time_slot_id' => $timeSlot->id,
            'discipline_id' => $discipline->id,
            'group_id' => $group->id,
        ];

        $response = $this->postJson('/api/lessons/create', $payload);
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Пара успешно создана',
                'status' => 201,
            ]);

        $duplicate = $this->postJson('/api/lessons/create', $payload);
        $duplicate->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Такая пара уже существует',
                'status' => 409,
            ]);
    }

    public function test_lessons_update_validates_and_updates(): void
    {
        $building = Building::create(['name' => 'Main', 'short_name' => 'M']);
        $classRoom = ClassRoom::create(['number' => '101', 'building_id' => $building->id]);
        $dayPartition = DayPartition::create(['start_time' => '08:30', 'end_time' => '10:00']);
        $timeSlot = TimeSlot::create([
            'week_type' => WeekType::Lower,
            'day' => Day::Tuesday,
            'day_partition_id' => $dayPartition->id,
        ]);
        $discipline = Discipline::create(['name' => 'Physics']);
        $teacher = Teacher::create(['name' => 'Jane Roe']);
        $institute = Institute::create(['name' => 'Tech']);
        $group = Group::create([
            'name' => 'B-02',
            'course' => Course::Second,
            'education_form' => EducationForm::Intramural,
            'institute_id' => $institute->id,
        ]);

        $createPayload = [
            'teacher_id' => $teacher->id,
            'class_room_id' => $classRoom->id,
            'time_slot_id' => $timeSlot->id,
            'discipline_id' => $discipline->id,
            'group_id' => $group->id,
        ];

        $created = $this->postJson('/api/lessons/create', $createPayload)->json('data');

        $updatePayload = [
            'group_id' => $group->id,
        ];

        $updateResponse = $this->putJson('/api/lessons/update/' . $created['id'], $updatePayload);
        $updateResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Пара была обновлена',
                'status' => 200,
            ]);
    }

    public function test_lessons_schedule_requires_validation(): void
    {
        $response = $this->getJson('/api/lessons/schedule');
        $response->assertStatus(422);
    }

    public function test_lessons_schedule_validates_group_ids_min_length(): void
    {
        $response = $this->getJson('/api/lessons/schedule?group_ids[]=&is_upper_week=1');
        $response->assertStatus(422);
    }

    public function test_lessons_schedule_validates_group_ids_exist(): void
    {
        $response = $this->getJson('/api/lessons/schedule?group_ids[]=999&is_upper_week=1');
        $response->assertStatus(422);
    }

    public function test_lessons_schedule_validates_is_upper_week_required(): void
    {
        $institute = Institute::create(['name' => 'Tech']);
        $group = Group::create([
            'name' => 'A-01',
            'course' => Course::First,
            'education_form' => EducationForm::Intramural,
            'institute_id' => $institute->id,
        ]);

        $response = $this->getJson('/api/lessons/schedule?group_ids[]=' . $group->id);
        $response->assertStatus(422);
    }

    public function test_lessons_schedule_returns_404_when_no_results(): void
    {
        $institute = Institute::create(['name' => 'Tech']);
        $group = Group::create([
            'name' => 'A-01',
            'course' => Course::First,
            'education_form' => EducationForm::Intramural,
            'institute_id' => $institute->id,
        ]);

        $response = $this->getJson('/api/lessons/schedule?group_ids[]=' . $group->id . '&is_upper_week=1');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Расписание не найдено',
                'status' => 404,
            ]);
    }

    public function test_lessons_schedule_returns_schedule_for_upper_week(): void
    {
        $building = Building::create(['name' => 'Main', 'short_name' => 'M']);
        $classRoom = ClassRoom::create(['number' => '101', 'building_id' => $building->id]);
        $dayPartition = DayPartition::create(['start_time' => '08:30', 'end_time' => '10:00']);
        $timeSlotUpper = TimeSlot::create([
            'week_type' => WeekType::Upper,
            'day' => Day::Monday,
            'day_partition_id' => $dayPartition->id,
        ]);
        $timeSlotLower = TimeSlot::create([
            'week_type' => WeekType::Lower,
            'day' => Day::Monday,
            'day_partition_id' => $dayPartition->id,
        ]);
        $discipline = Discipline::create(['name' => 'Math']);
        $teacher = Teacher::create(['name' => 'John Doe']);
        $institute = Institute::create(['name' => 'Tech']);
        $group = Group::create([
            'name' => 'A-01',
            'course' => Course::First,
            'education_form' => EducationForm::Intramural,
            'institute_id' => $institute->id,
        ]);

        $lessonUpper = Lesson::create([
            'teacher_id' => $teacher->id,
            'class_room_id' => $classRoom->id,
            'time_slot_id' => $timeSlotUpper->id,
            'discipline_id' => $discipline->id,
            'group_id' => $group->id,
        ]);

        $lessonLower = Lesson::create([
            'teacher_id' => $teacher->id,
            'class_room_id' => $classRoom->id,
            'time_slot_id' => $timeSlotLower->id,
            'discipline_id' => $discipline->id,
            'group_id' => $group->id,
        ]);

        $response = $this->getJson('/api/lessons/schedule?group_ids[]=' . $group->id . '&is_upper_week=1');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Расписание получено',
                'status' => 200,
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $lessonUpper->id)
            ->assertJsonPath('data.0.group_id', $group->id);
    }

    public function test_lessons_schedule_returns_schedule_for_lower_week(): void
    {
        $building = Building::create(['name' => 'Main', 'short_name' => 'M']);
        $classRoom = ClassRoom::create(['number' => '101', 'building_id' => $building->id]);
        $dayPartition = DayPartition::create(['start_time' => '08:30', 'end_time' => '10:00']);
        $timeSlotUpper = TimeSlot::create([
            'week_type' => WeekType::Upper,
            'day' => Day::Monday,
            'day_partition_id' => $dayPartition->id,
        ]);
        $timeSlotLower = TimeSlot::create([
            'week_type' => WeekType::Lower,
            'day' => Day::Monday,
            'day_partition_id' => $dayPartition->id,
        ]);
        $discipline = Discipline::create(['name' => 'Math']);
        $teacher = Teacher::create(['name' => 'John Doe']);
        $institute = Institute::create(['name' => 'Tech']);
        $group = Group::create([
            'name' => 'A-01',
            'course' => Course::First,
            'education_form' => EducationForm::Intramural,
            'institute_id' => $institute->id,
        ]);

        $lessonUpper = Lesson::create([
            'teacher_id' => $teacher->id,
            'class_room_id' => $classRoom->id,
            'time_slot_id' => $timeSlotUpper->id,
            'discipline_id' => $discipline->id,
            'group_id' => $group->id,
        ]);

        $lessonLower = Lesson::create([
            'teacher_id' => $teacher->id,
            'class_room_id' => $classRoom->id,
            'time_slot_id' => $timeSlotLower->id,
            'discipline_id' => $discipline->id,
            'group_id' => $group->id,
        ]);

        $response = $this->getJson('/api/lessons/schedule?group_ids[]=' . $group->id . '&is_upper_week=0');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Расписание получено',
                'status' => 200,
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $lessonLower->id)
            ->assertJsonPath('data.0.group_id', $group->id);
    }

    public function test_lessons_schedule_returns_schedule_for_multiple_groups(): void
    {
        $building = Building::create(['name' => 'Main', 'short_name' => 'M']);
        $classRoom = ClassRoom::create(['number' => '101', 'building_id' => $building->id]);
        $dayPartition = DayPartition::create(['start_time' => '08:30', 'end_time' => '10:00']);
        $timeSlot = TimeSlot::create([
            'week_type' => WeekType::Upper,
            'day' => Day::Monday,
            'day_partition_id' => $dayPartition->id,
        ]);
        $discipline = Discipline::create(['name' => 'Math']);
        $teacher = Teacher::create(['name' => 'John Doe']);
        $institute = Institute::create(['name' => 'Tech']);

        $group1 = Group::create([
            'name' => 'A-01',
            'course' => Course::First,
            'education_form' => EducationForm::Intramural,
            'institute_id' => $institute->id,
        ]);

        $group2 = Group::create([
            'name' => 'A-02',
            'course' => Course::First,
            'education_form' => EducationForm::Intramural,
            'institute_id' => $institute->id,
        ]);

        $lesson1 = Lesson::create([
            'teacher_id' => $teacher->id,
            'class_room_id' => $classRoom->id,
            'time_slot_id' => $timeSlot->id,
            'discipline_id' => $discipline->id,
            'group_id' => $group1->id,
        ]);

        $lesson2 = Lesson::create([
            'teacher_id' => $teacher->id,
            'class_room_id' => $classRoom->id,
            'time_slot_id' => $timeSlot->id,
            'discipline_id' => $discipline->id,
            'group_id' => $group2->id,
        ]);

        $response = $this->getJson('/api/lessons/schedule?group_ids[]=' . $group1->id . '&group_ids[]=' . $group2->id . '&is_upper_week=1');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Расписание получено',
                'status' => 200,
            ])
            ->assertJsonCount(2, 'data');

        $data = $response->json('data');
        $groupIds = collect($data)->pluck('group_id')->toArray();
        $this->assertContains($group1->id, $groupIds);
        $this->assertContains($group2->id, $groupIds);
    }

    public function test_lessons_schedule_includes_relations(): void
    {
        $building = Building::create(['name' => 'Main', 'short_name' => 'M']);
        $classRoom = ClassRoom::create(['number' => '101', 'building_id' => $building->id]);
        $dayPartition = DayPartition::create(['start_time' => '08:30', 'end_time' => '10:00']);
        $timeSlot = TimeSlot::create([
            'week_type' => WeekType::Upper,
            'day' => Day::Monday,
            'day_partition_id' => $dayPartition->id,
        ]);
        $discipline = Discipline::create(['name' => 'Math']);
        $teacher = Teacher::create(['name' => 'John Doe']);
        $institute = Institute::create(['name' => 'Tech']);
        $group = Group::create([
            'name' => 'A-01',
            'course' => Course::First,
            'education_form' => EducationForm::Intramural,
            'institute_id' => $institute->id,
        ]);

        Lesson::create([
            'teacher_id' => $teacher->id,
            'class_room_id' => $classRoom->id,
            'time_slot_id' => $timeSlot->id,
            'discipline_id' => $discipline->id,
            'group_id' => $group->id,
        ]);

        $response = $this->getJson('/api/lessons/schedule?group_ids[]=' . $group->id . '&is_upper_week=1');

        $response->assertStatus(200);
        $data = $response->json('data.0');

        $this->assertArrayHasKey('teacher', $data);
        $this->assertArrayHasKey('class_room', $data);
        $this->assertArrayHasKey('time_slot', $data);
        $this->assertArrayHasKey('discipline', $data);
        $this->assertArrayHasKey('group', $data);

        $this->assertEquals($teacher->id, $data['teacher']['id']);
        $this->assertEquals($classRoom->id, $data['class_room']['id']);
        $this->assertEquals($timeSlot->id, $data['time_slot']['id']);
        $this->assertEquals($discipline->id, $data['discipline']['id']);
        $this->assertEquals($group->id, $data['group']['id']);
    }
}


