<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use Illuminate\Http\Request;

class ClassRoomController extends Controller
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

    public function searchClassRooms(Request $request)
    {
        $request->validate([
            "number" => "nullable|string|min:1",
            "building_id" => "required|integer",
        ]);

        $query = ClassRoom::query();

        $query->whereHas('buildings', function ($q) use ($request) {
            $q->where('buildings.id', $request->building_id);
        });

        if ($request->filled('number')) {
            $classRooms = $query->where("number", 'like', '%' . $request->number . '%')->get();
        } else {
            $classRooms = $query->limit(10)->get();
        }

        if ($classRooms->isEmpty()) {
            return errorResponse('Аудитории не найдены', 404);
        }

        return successResponse($classRooms, 'Аудитории получены', 200);
    }
}
