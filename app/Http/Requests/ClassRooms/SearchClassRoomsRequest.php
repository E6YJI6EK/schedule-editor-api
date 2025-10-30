<?php

namespace App\Http\Requests\ClassRooms;

use Illuminate\Foundation\Http\FormRequest;

class SearchClassRoomsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number' => 'nullable|string|min:1',
            'building_id' => 'required|integer|exists:buildings,id',
        ];
    }
}


