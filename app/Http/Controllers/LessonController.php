<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lessons\CreateLessonRequest;
use App\Http\Requests\Lessons\ExportScheduleRequest;
use App\Http\Requests\Lessons\GetScheduleByClassroomRequest;
use App\Http\Requests\Lessons\GetScheduleByTeacherRequest;
use App\Http\Requests\Lessons\GetScheduleRequest;
use App\Http\Requests\Lessons\GetTimeSlotRequest;
use App\Http\Requests\Lessons\UpdateLessonRequest;
use App\Models\Lesson;
use App\Services\LessonService;
use App\Services\ScheduleExportService;
use Barryvdh\DomPDF\Facade\Pdf;

class LessonController extends Controller
{
    public function __construct(
        private readonly LessonService $lessonService,
        private readonly ScheduleExportService $exportService,
    ) {
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

    public function getScheduleByTeacher(GetScheduleByTeacherRequest $request)
    {
        $validated = $request->validated();
        $lessons = $this->lessonService->getScheduleByTeacher(
            $validated['teacher_id'],
            $validated['is_upper_week']
        );

        if ($lessons->isEmpty()) {
            return errorResponse('Расписание не найдено', 404);
        }

        return successResponse($lessons, 'Расписание получено', 200);
    }

    public function getScheduleByClassroom(GetScheduleByClassroomRequest $request)
    {
        $validated = $request->validated();
        $lessons = $this->lessonService->getScheduleByClassroom(
            $validated['classroom_id'],
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

    public function exportExcel(ExportScheduleRequest $request)
    {
        $groupIds = $request->validated()['group_ids'];
        $path = $this->exportService->exportExcel($groupIds);
        $date = now()->format('d_m_Y');

        return response()->download($path, "Расписание_{$date}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }

    public function exportPdf(ExportScheduleRequest $request)
    {
        $groupIds = $request->validated()['group_ids'];
        $data = $this->exportService->buildViewData($groupIds);
        $date = now()->format('d_m_Y');

        $pdf = Pdf::loadView('schedule-pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download("Расписание_{$date}.pdf");
    }
}
