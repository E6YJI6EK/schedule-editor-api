<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Exception;
use Illuminate\Http\Request;

class LessonController extends Controller
{
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
    public function create(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|integer|exists:teachers,id',
            'class_room_id' => 'required|integer|exists:class_rooms,id',
            'time_slot_id' => 'required|integer|exists:time_slots,id',
            'discipline_id' => 'required|integer|exists:disciplines,id',
            'group_id' => 'required|integer|exists:groups,id'
        ]);

        // Проверка на дубликат (если нужно)
        $existingLesson = Lesson::where([
            'teacher_id' => $request->teacher_id,
            'class_room_id' => $request->class_room_id,
            'time_slot_id' => $request->time_slot_id,
            'discipline_id' => $request->discipline_id,
            'group_id' => $request->group_id
        ])->exists();

        if ($existingLesson) {
            return errorResponse('Такая пара уже существует', 409);
        }

        try {
            $lesson = Lesson::create($request->all());
            return successResponse($lesson, 'Пара успешно создана', 201);
        } catch (Exception $e) {
            return errorResponse('Ошибка при создании пары', 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'teacher_id' => 'integer|exists:teachers,id',
                'class_room_id' => 'integer|exists:class_rooms,id',
                'time_slot_id' => 'integer|exists:time_slots,id',
                'discipline_id' => 'integer|exists:disciplines,id',
                'group_id' => 'integer|exists:groups,id'
            ]);
            $lesson = Lesson::findOrFail($id);
            $lesson->update($request->all());
            return successResponse($lesson, 'Пара была обновлена', 200);
        } catch (Exception $e) {
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
