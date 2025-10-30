<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lessons\CreateLessonRequest;
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
}
