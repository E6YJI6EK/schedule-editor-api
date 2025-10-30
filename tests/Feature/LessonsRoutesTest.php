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
use App\Models\Teacher;
use App\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonsRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_lessons_create_requires_validation(): void
    {
        $response = $this->postJson('/lessons/create', []);
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

        $response = $this->postJson('/lessons/create', $payload);
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Пара успешно создана',
                'status' => 201,
            ]);

        $duplicate = $this->postJson('/lessons/create', $payload);
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

        $created = $this->postJson('/lessons/create', $createPayload)->json('data');

        $updatePayload = [
            'group_id' => $group->id,
        ];

        $updateResponse = $this->putJson('/lessons/update/' . $created['id'], $updatePayload);
        $updateResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Пара была обновлена',
                'status' => 200,
            ]);
    }
}


