<?php

namespace App\Http\Requests\ClassRooms;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number' => 'required|string|max:50',
            'building_id' => 'required|integer|exists:buildings,id',
        ];
    }
}
