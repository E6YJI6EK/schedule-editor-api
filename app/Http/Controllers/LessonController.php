<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lessons\CreateLessonRequest;
use App\Http\Requests\Lessons\GetScheduleRequest;
use App\Services\LessonService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LessonController extends Controller
{
    public function __construct(private readonly LessonService $lessonService)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(\Illuminate\Http\Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\Illuminate\Http\Request $request, string $id)
    {
        try {
            $request->validate([
                'teacher_id' => 'integer|exists:teachers,id',
                'class_room_id' => 'integer|exists:class_rooms,id',
                'time_slot_id' => 'integer|exists:time_slots,id',
                'discipline_id' => 'integer|exists:disciplines,id',
                'group_id' => 'integer|exists:groups,id'
            ]);
            $lesson = \App\Models\Lesson::findOrFail($id);
            $lesson->update($request->all());
            return successResponse($lesson, 'Пара была обновлена', 200);
        } catch (NotFoundHttpException $e) {
            return errorResponse('Пара не найдена', 404, $e->getMessage());
        } catch (\Exception $e) {
            return errorResponse('Ошибка', 500, '');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Получить расписание для групп
     */
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

    /**
     * Получить ID временного слота по параметрам
     */
    public function getTimeSlot(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'week_type' => 'required|string|in:upper,lower',
            'day' => 'required|integer|min:1|max:7',
            'day_partition_id' => 'required|integer|min:1|max:6',
        ]);

        $timeSlot = \App\Models\TimeSlot::where([
            'week_type' => $request->week_type,
            'day' => $request->day,
            'day_partition_id' => $request->day_partition_id,
        ])->first();

        if (!$timeSlot) {
            return errorResponse('Временной слот не найден', 404);
        }

        return successResponse(['id' => $timeSlot->id], 'Временной слот найден', 200);
    }
}
