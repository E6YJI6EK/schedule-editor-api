<?php

namespace App\Services;

use App\Enums\WeekType;
use App\Models\Lesson;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class LessonService
{
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
            return ['lesson' => $lesson];
        } catch (Exception $e) {
            return ['exception' => $e->getMessage()];
        }
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
                'group'
            ])
            ->get();
    }
}


