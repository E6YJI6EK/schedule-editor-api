<?php

namespace App\Http\Requests\Buildings;

use Illuminate\Foundation\Http\FormRequest;

class StoreBuildingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
        ];
    }
}
