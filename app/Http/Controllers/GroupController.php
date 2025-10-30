<?php

namespace App\Http\Controllers;

use App\Enums\Course;
use App\Enums\EducationForm;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GroupController extends Controller
{
    public function searchGroups(Request $request)
    {
        $request->validate([
            "name" => "nullable|string|min:2",
            "course" => ['required', Rule::in(Course::cases())],
            "education_form" => ["required", Rule::in(EducationForm::cases())],
            "institute_id" => "required|integer",
        ]);

        $query = Group::query();

        $query->whereHas('disciplines', function ($q) use ($request) {
            $q->where('disciplines.id', $request->discipline_id);
        });

        if ($request->filled('name')) {
            $teachers = $query->where("name", 'like', '%' . $request->name . '%')->get();
        } else {
            $teachers = $query->limit(10)->get();
        }

        if ($teachers->isEmpty()) {
            return errorResponse('Учителя не найдены', 404);
        }

        return successResponse($teachers, 'Учителя получены', 200);
    }
}
