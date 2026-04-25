<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\ClassRoomController;
use App\Http\Controllers\DisciplineController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\TeacherController;
// Public
Route::get('/', [AppController::class, 'index'])->name('index');
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

Route::get('/teachers/search', [TeacherController::class, 'searchTeachers'])->name('teachers.search');
Route::get('/disciplines/search', [DisciplineController::class, 'searchDisciplines'])->name('disciplines.search');
Route::get('/class-rooms/search', [ClassRoomController::class, 'searchClassRooms'])->name('classRooms.search');
Route::get('/buildings/search', [BuildingController::class, 'searchBuildings'])->name('buildings.search');
Route::get('/groups/search', [GroupController::class, 'searchGroups'])->name('groups.search');
Route::get('/groups/search-by-name', [GroupController::class, 'searchGroupsByName'])->name('groups.searchByName');
Route::get('/lessons/schedule', [LessonController::class, 'getSchedule'])->name('lessons.schedule');
Route::get('/lessons/time-slot', [LessonController::class, 'getTimeSlot'])->name('lessons.timeSlot');

// Authenticated
Route::middleware('auth')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Employee + Admin
    Route::middleware('role:ADMIN,EMPLOYEE')->group(function () {
        Route::post('/lessons/create', [LessonController::class, 'create'])->name('lessons.create');
        Route::put('/lessons/update/{id}', [LessonController::class, 'update'])->name('lessons.update');
    });

    // Admin only
    Route::middleware('role:ADMIN')->group(function () {
        Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
        Route::delete('/employees/{id}', [AuthController::class, 'deleteEmployee'])->name('employees.delete');
    });
});
