<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Http\Request;

class BuildingController extends Controller
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
    public function create()
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function searchBuildings(Request $request)
    {
        $request->validate([
            "name" => "nullable|string",
        ]);

        $query = Building::query();

        if ($request->filled("name")) {
            $buildings = $query->where("name", 'like', '%' . $request->name . '%')->get();
        } else {
            $buildings = $query->limit(10)->get();
        }

        if ($buildings->isEmpty()) {
            return errorResponse('Корпуса не найдены', 404);
        }

        return successResponse($buildings, 'Корпуса получены', 200);
    }
}
