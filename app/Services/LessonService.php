<?php

namespace App\Services;

use App\Models\Lesson;
use Exception;

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
}


