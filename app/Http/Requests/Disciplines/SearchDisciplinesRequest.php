<?php

namespace App\Http\Requests\Disciplines;

use Illuminate\Foundation\Http\FormRequest;

class SearchDisciplinesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string',
        ];
    }
}


