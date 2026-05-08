<?php

namespace App\Http\Requests\ClassRooms;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number' => 'sometimes|required|string|max:50',
            'building_id' => 'sometimes|required|integer|exists:buildings,id',
        ];
    }
}
