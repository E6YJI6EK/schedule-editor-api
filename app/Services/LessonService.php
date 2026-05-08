<?php

namespace App\Services;

use App\Enums\WeekType;
use App\Models\Lesson;
use App\Models\TimeSlot;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class LessonService
{
    public function all(): Collection
    {
        return Lesson::with(['teacher', 'classRoom.building', 'timeSlot.dayPartition', 'discipline', 'group'])
            ->get();
    }

    public function find(int $id): Lesson
    {
        return Lesson::with(['teacher', 'classRoom.building', 'timeSlot.dayPartition', 'discipline', 'group'])
            ->findOrFail($id);
    }

    public function create(array $data): array
    {
        $exists = Lesson::where([
            'teacher_id' => $data['teacher_id'],
            'class_room_id' => $data['class_room_id'],
            'time_slot_id' => $data['time_slot_id'],
            'discipline_id' => $data['discipline_id'],
            'group_id' => $data['group_id'],
        ])->exists();

        if ($exists) {
            return ['error' => 'duplicate'];
        }

        try {
            $lesson = Lesson::create($data);
            return ['lesson' => $lesson->load(['teacher', 'classRoom.building', 'timeSlot.dayPartition', 'discipline', 'group'])];
        } catch (Exception $e) {
            return ['exception' => $e->getMessage()];
        }
    }

    public function update(Lesson $lesson, array $data): Lesson
    {
        $lesson->update($data);

        return $lesson->load(['teacher', 'classRoom.building', 'timeSlot.dayPartition', 'discipline', 'group']);
    }

    public function delete(Lesson $lesson): void
    {
        $lesson->delete();
    }

    public function getSchedule(array $groupIds, bool $isUpperWeek): Collection
    {
        $weekType = $isUpperWeek ? WeekType::Upper : WeekType::Lower;

        return Lesson::whereIn('group_id', $groupIds)
            ->whereHas('timeSlot', function ($query) use ($weekType) {
                $query->where('week_type', $weekType);
            })
            ->with([
                'teacher',
                'classRoom.building',
                'timeSlot.dayPartition',
                'discipline',
                'group',
            ])
            ->get();
    }

    public function getScheduleByTeacher(int $teacherId, bool $isUpperWeek): Collection
    {
        $weekType = $isUpperWeek ? WeekType::Upper : WeekType::Lower;

        return Lesson::where('teacher_id', $teacherId)
            ->whereHas('timeSlot', function ($query) use ($weekType) {
                $query->where('week_type', $weekType);
            })
            ->with([
                'teacher',
                'classRoom.building',
                'timeSlot.dayPartition',
                'discipline',
                'group',
            ])
            ->get();
    }

    public function getScheduleByClassroom(int $classRoomId, bool $isUpperWeek): Collection
    {
        $weekType = $isUpperWeek ? WeekType::Upper : WeekType::Lower;

        return Lesson::where('class_room_id', $classRoomId)
            ->whereHas('timeSlot', function ($query) use ($weekType) {
                $query->where('week_type', $weekType);
            })
            ->with([
                'teacher',
                'classRoom.building',
                'timeSlot.dayPartition',
                'discipline',
                'group',
            ])
            ->get();
    }

    public function findTimeSlot(array $filters): ?TimeSlot
    {
        return TimeSlot::where([
            'week_type' => $filters['week_type'],
            'day' => $filters['day'],
            'day_partition_id' => $filters['day_partition_id'],
        ])->first();
    }
}
