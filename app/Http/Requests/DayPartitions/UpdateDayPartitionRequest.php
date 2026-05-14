<?php

namespace App\Http\Requests\DayPartitions;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDayPartitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
        ];
    }
}
