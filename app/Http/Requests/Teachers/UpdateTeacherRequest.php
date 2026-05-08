<?php

namespace App\Http\Requests\Teachers;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'discipline_ids' => 'sometimes|array',
            'discipline_ids.*' => 'integer|exists:disciplines,id',
        ];
    }
}
