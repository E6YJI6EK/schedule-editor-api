<?php

namespace App\Http\Requests\Teachers;

use Illuminate\Foundation\Http\FormRequest;

class SearchTeachersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|min:2',
            'discipline_id' => 'required|integer|exists:disciplines,id',
        ];
    }
}


