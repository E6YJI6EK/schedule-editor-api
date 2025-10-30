<?php

namespace App\Http\Requests\Groups;

use App\Enums\Course;
use App\Enums\EducationForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchGroupsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|min:2',
            'course' => ['required', Rule::in(Course::cases())],
            'education_form' => ['required', Rule::in(EducationForm::cases())],
            'institute_id' => 'required|integer|exists:institutes,id',
        ];
    }
}


