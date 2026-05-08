<?php

namespace App\Http\Requests\Lessons;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacher_id' => 'sometimes|integer|exists:teachers,id',
            'class_room_id' => 'sometimes|integer|exists:class_rooms,id',
            'time_slot_id' => 'sometimes|integer|exists:time_slots,id',
            'discipline_id' => 'sometimes|integer|exists:disciplines,id',
            'group_id' => 'sometimes|integer|exists:groups,id',
        ];
    }
}
