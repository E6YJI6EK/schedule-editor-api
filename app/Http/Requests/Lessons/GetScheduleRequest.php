<?php

namespace App\Http\Requests\Lessons;

use Illuminate\Foundation\Http\FormRequest;

class GetScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_ids' => 'required|array|min:1',
            'group_ids.*' => 'required|integer|exists:groups,id',
            'is_upper_week' => 'required|boolean',
        ];
    }
}

