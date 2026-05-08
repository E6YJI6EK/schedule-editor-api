<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lessons\CreateLessonRequest;
use App\Http\Requests\Lessons\GetScheduleRequest;
use App\Http\Requests\Lessons\GetTimeSlotRequest;
use App\Http\Requests\Lessons\UpdateLessonRequest;
use App\Models\Lesson;
use App\Services\LessonService;

class LessonController extends Controller
{
    public function __construct(private readonly LessonService $lessonService)
    {
    }

    public function index()
    {
        return successResponse($this->lessonService->all(), 'Пары получены', 200);
    }

    public function show(Lesson $lesson)
    {
        return successResponse($this->lessonService->find($lesson->id), 'Пара получена', 200);
    }

    public function create(CreateLessonRequest $request)
    {
        $result = $this->lessonService->create($request->validated());

        if (isset($result['error']) && $result['error'] === 'duplicate') {
            return errorResponse('Такая пара уже существует', 409);
        }

        if (isset($result['exception'])) {
            return errorResponse('Ошибка при создании пары', 500, $result['exception']);
        }

        return successResponse($result['lesson'], 'Пара успешно создана', 201);
    }

    public function update(UpdateLessonRequest $request, Lesson $lesson)
    {
        $updated = $this->lessonService->update($lesson, $request->validated());

        return successResponse($updated, 'Пара была обновлена', 200);
    }

    public function destroy(Lesson $lesson)
    {
        $this->lessonService->delete($lesson);

        return successResponse(null, 'Пара удалена', 200);
    }

    public function getSchedule(GetScheduleRequest $request)
    {
        $validated = $request->validated();
        $lessons = $this->lessonService->getSchedule(
            $validated['group_ids'],
            $validated['is_upper_week']
        );

        if ($lessons->isEmpty()) {
            return errorResponse('Расписание не найдено', 404);
        }

        return successResponse($lessons, 'Расписание получено', 200);
    }

    public function getTimeSlot(GetTimeSlotRequest $request)
    {
        $timeSlot = $this->lessonService->findTimeSlot($request->validated());

        if (!$timeSlot) {
            return errorResponse('Временной слот не найден', 404);
        }

        return successResponse(['id' => $timeSlot->id], 'Временной слот найден', 200);
    }
}
