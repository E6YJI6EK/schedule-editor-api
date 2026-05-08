<?php

namespace App\Http\Requests\Lessons;

use Illuminate\Foundation\Http\FormRequest;

class GetScheduleByClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classroom_id' => 'required|integer|exists:class_rooms,id',
            'is_upper_week' => 'required|boolean',
        ];
    }
}
