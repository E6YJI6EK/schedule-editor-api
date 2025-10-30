<?php

namespace App\Http\Controllers;

use App\Http\Requests\Disciplines\SearchDisciplinesRequest;
use App\Models\Discipline;
use App\Services\DisciplineService;

class DisciplineController extends Controller
{
    public function __construct(private readonly DisciplineService $disciplineService)
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
    public function create()
    {
        //
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
    public function show(Discipline $discipline)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Discipline $discipline)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\Illuminate\Http\Request $request, Discipline $discipline)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Discipline $discipline)
    {
        //
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
