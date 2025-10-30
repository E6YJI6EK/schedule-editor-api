<?php

namespace App\Http\Controllers;

use App\Http\Requests\Teachers\SearchTeachersRequest;
use App\Services\TeacherService;

class TeacherController extends Controller
{
    public function __construct(private readonly TeacherService $teacherService)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->teacherService->paginateLatest(10);
    }

    public function searchTeachers(SearchTeachersRequest $request)
    {
        $teachers = $this->teacherService->search($request->validated());

        if ($teachers->isEmpty()) {
            return errorResponse('Учителя не найдены', 404);
        }

        return successResponse($teachers, 'Учителя получены', 200);
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
    public function show(\App\Models\Teacher $teacher)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(\App\Models\Teacher $teacher)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\Illuminate\Http\Request $request, \App\Models\Teacher $teacher)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(\App\Models\Teacher $teacher)
    {
        //
    }
}
