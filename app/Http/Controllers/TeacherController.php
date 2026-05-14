<?php

namespace App\Http\Controllers;

use App\Http\Requests\Teachers\SearchTeachersRequest;
use App\Http\Requests\Teachers\StoreTeacherRequest;
use App\Http\Requests\Teachers\UpdateTeacherRequest;
use App\Models\Teacher;
use App\Services\TeacherService;

class TeacherController extends Controller
{
    public function __construct(private readonly TeacherService $teacherService)
    {
    }

    public function index()
    {
        return successResponse($this->teacherService->all(), 'Преподаватели получены', 200);
    }

    public function store(StoreTeacherRequest $request)
    {
        $teacher = $this->teacherService->store($request->validated());

        return successResponse($teacher, 'Преподаватель создан', 201);
    }

    public function show(Teacher $teacher)
    {
        return successResponse($this->teacherService->find($teacher->id), 'Преподаватель получен', 200);
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        $updated = $this->teacherService->update($teacher, $request->validated());

        return successResponse($updated, 'Преподаватель обновлён', 200);
    }

    public function destroy(Teacher $teacher)
    {
        $this->teacherService->delete($teacher);

        return successResponse(null, 'Преподаватель удалён', 200);
    }

    public function searchTeachers(SearchTeachersRequest $request)
    {
        $teachers = $this->teacherService->search($request->validated());

        if ($teachers->isEmpty()) {
            return errorResponse('Учителя не найдены', 404);
        }

        return successResponse($teachers, 'Учителя получены', 200);
    }
}
