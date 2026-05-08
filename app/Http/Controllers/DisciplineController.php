<?php

namespace App\Http\Controllers;

use App\Http\Requests\Disciplines\SearchDisciplinesRequest;
use App\Http\Requests\Disciplines\StoreDisciplineRequest;
use App\Http\Requests\Disciplines\UpdateDisciplineRequest;
use App\Models\Discipline;
use App\Services\DisciplineService;

class DisciplineController extends Controller
{
    public function __construct(private readonly DisciplineService $disciplineService)
    {
    }

    public function index()
    {
        return successResponse($this->disciplineService->all(), 'Дисциплины получены', 200);
    }

    public function store(StoreDisciplineRequest $request)
    {
        $discipline = $this->disciplineService->store($request->validated());

        return successResponse($discipline, 'Дисциплина создана', 201);
    }

    public function show(Discipline $discipline)
    {
        return successResponse($this->disciplineService->find($discipline->id), 'Дисциплина получена', 200);
    }

    public function update(UpdateDisciplineRequest $request, Discipline $discipline)
    {
        $updated = $this->disciplineService->update($discipline, $request->validated());

        return successResponse($updated, 'Дисциплина обновлена', 200);
    }

    public function destroy(Discipline $discipline)
    {
        $this->disciplineService->delete($discipline);

        return successResponse(null, 'Дисциплина удалена', 200);
    }

    public function searchDisciplines(SearchDisciplinesRequest $request)
    {
        $disciplines = $this->disciplineService->search($request->validated());

        if ($disciplines->isEmpty()) {
            return errorResponse('Дисциплины не найдены', 404);
        }

        return successResponse($disciplines, 'Дисциплины получены', 200);
    }
}
