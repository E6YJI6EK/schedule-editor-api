<?php

namespace App\Http\Controllers;

use App\Http\Requests\DayPartitions\StoreDayPartitionRequest;
use App\Http\Requests\DayPartitions\UpdateDayPartitionRequest;
use App\Models\DayPartition;
use App\Services\DayPartitionService;

class DayPartitionController extends Controller
{
    public function __construct(private readonly DayPartitionService $dayPartitionService)
    {
    }

    public function index()
    {
        return successResponse($this->dayPartitionService->all(), 'Временные промежутки получены', 200);
    }

    public function store(StoreDayPartitionRequest $request)
    {
        $dayPartition = $this->dayPartitionService->store($request->validated());

        return successResponse($dayPartition, 'Временной промежуток создан', 201);
    }

    public function update(UpdateDayPartitionRequest $request, DayPartition $dayPartition)
    {
        $updated = $this->dayPartitionService->update($dayPartition, $request->validated());

        return successResponse($updated, 'Временной промежуток обновлён', 200);
    }

    public function destroy(DayPartition $dayPartition)
    {
        $this->dayPartitionService->delete($dayPartition);

        return successResponse(null, 'Временной промежуток удалён', 200);
    }
}
