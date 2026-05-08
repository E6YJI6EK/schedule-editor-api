<?php

namespace App\Http\Requests\Buildings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBuildingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'short_name' => 'sometimes|required|string|max:50',
        ];
    }
}
