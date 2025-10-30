<?php

namespace App\Http\Requests\Buildings;

use Illuminate\Foundation\Http\FormRequest;

class SearchBuildingsRequest extends FormRequest
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


