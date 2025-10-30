<?php

namespace App\Http\Controllers;

use App\Http\Requests\Buildings\SearchBuildingsRequest;
use App\Services\BuildingService;

class BuildingController extends Controller
{
    public function __construct(private readonly BuildingService $buildingService)
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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
