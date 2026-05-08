<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClassRooms\SearchClassRoomsRequest;
use App\Http\Requests\ClassRooms\StoreClassRoomRequest;
use App\Http\Requests\ClassRooms\UpdateClassRoomRequest;
use App\Models\ClassRoom;
use App\Services\ClassRoomService;

class ClassRoomController extends Controller
{
    public function __construct(private readonly ClassRoomService $classRoomService)
    {
    }

    public function index()
    {
        return successResponse($this->classRoomService->all(), 'Аудитории получены', 200);
    }

    public function store(StoreClassRoomRequest $request)
    {
        $classRoom = $this->classRoomService->store($request->validated());

        return successResponse($classRoom, 'Аудитория создана', 201);
    }

    public function show(ClassRoom $classRoom)
    {
        return successResponse($this->classRoomService->find($classRoom->id), 'Аудитория получена', 200);
    }

    public function update(UpdateClassRoomRequest $request, ClassRoom $classRoom)
    {
        $updated = $this->classRoomService->update($classRoom, $request->validated());

        return successResponse($updated, 'Аудитория обновлена', 200);
    }

    public function destroy(ClassRoom $classRoom)
    {
        $this->classRoomService->delete($classRoom);

        return successResponse(null, 'Аудитория удалена', 200);
    }

    public function searchClassRooms(SearchClassRoomsRequest $request)
    {
        $classRooms = $this->classRoomService->search($request->validated());

        if ($classRooms->isEmpty()) {
            return errorResponse('Аудитории не найдены', 404);
        }

        return successResponse($classRooms, 'Аудитории получены', 200);
    }
}
