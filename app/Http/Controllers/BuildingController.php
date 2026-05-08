<?php

namespace App\Http\Controllers;

use App\Http\Requests\Buildings\SearchBuildingsRequest;
use App\Http\Requests\Buildings\StoreBuildingRequest;
use App\Http\Requests\Buildings\UpdateBuildingRequest;
use App\Models\Building;
use App\Services\BuildingService;

class BuildingController extends Controller
{
    public function __construct(private readonly BuildingService $buildingService)
    {
    }

    public function index()
    {
        return successResponse($this->buildingService->all(), 'Корпуса получены', 200);
    }

    public function store(StoreBuildingRequest $request)
    {
        $building = $this->buildingService->store($request->validated());

        return successResponse($building, 'Корпус создан', 201);
    }

    public function show(Building $building)
    {
        return successResponse($this->buildingService->find($building->id), 'Корпус получен', 200);
    }

    public function update(UpdateBuildingRequest $request, Building $building)
    {
        $updated = $this->buildingService->update($building, $request->validated());

        return successResponse($updated, 'Корпус обновлён', 200);
    }

    public function destroy(Building $building)
    {
        $this->buildingService->delete($building);

        return successResponse(null, 'Корпус удалён', 200);
    }

    public function searchBuildings(SearchBuildingsRequest $request)
    {
        $buildings = $this->buildingService->search($request->validated());

        if ($buildings->isEmpty()) {
            return errorResponse('Корпуса не найдены', 404);
        }

        return successResponse($buildings, 'Корпуса получены', 200);
    }
}
