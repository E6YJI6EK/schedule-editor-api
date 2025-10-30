<?php

namespace App\Http\Requests\Lessons;

use Illuminate\Foundation\Http\FormRequest;

class CreateLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacher_id' => 'required|integer|exists:teachers,id',
            'class_room_id' => 'required|integer|exists:class_rooms,id',
            'time_slot_id' => 'required|integer|exists:time_slots,id',
            'discipline_id' => 'required|integer|exists:disciplines,id',
            'group_id' => 'required|integer|exists:groups,id',
        ];
    }
}


