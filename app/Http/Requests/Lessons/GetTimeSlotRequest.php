<?php

namespace App\Http\Requests\Lessons;

use Illuminate\Foundation\Http\FormRequest;

class GetTimeSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'week_type' => 'required|string|in:upper,lower',
            'day' => 'required|integer|min:1|max:6',
            'day_partition_id' => 'required|integer|min:1|max:6',
        ];
    }
}
