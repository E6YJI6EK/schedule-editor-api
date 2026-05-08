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

// Search (public)
Route::get('/teachers/search', [TeacherController::class, 'searchTeachers'])->name('teachers.search');
Route::get('/disciplines/search', [DisciplineController::class, 'searchDisciplines'])->name('disciplines.search');
Route::get('/class-rooms/search', [ClassRoomController::class, 'searchClassRooms'])->name('classRooms.search');
Route::get('/buildings/search', [BuildingController::class, 'searchBuildings'])->name('buildings.search');
Route::get('/groups/search', [GroupController::class, 'searchGroups'])->name('groups.search');
Route::get('/groups/search-by-name', [GroupController::class, 'searchGroupsByName'])->name('groups.searchByName');

// Read (public)
Route::get('/buildings', [BuildingController::class, 'index'])->name('buildings.index');
Route::get('/buildings/{building}', [BuildingController::class, 'show'])->name('buildings.show');
Route::get('/class-rooms', [ClassRoomController::class, 'index'])->name('classRooms.index');
Route::get('/class-rooms/{classRoom}', [ClassRoomController::class, 'show'])->name('classRooms.show');
Route::get('/disciplines', [DisciplineController::class, 'index'])->name('disciplines.index');
Route::get('/disciplines/{discipline}', [DisciplineController::class, 'show'])->name('disciplines.show');
Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');
Route::get('/lessons/schedule', [LessonController::class, 'getSchedule'])->name('lessons.schedule');
Route::get('/lessons/schedule/by-teacher', [LessonController::class, 'getScheduleByTeacher'])->name('lessons.schedule.byTeacher');
Route::get('/lessons/schedule/by-classroom', [LessonController::class, 'getScheduleByClassroom'])->name('lessons.schedule.byClassroom');
Route::get('/lessons/time-slot', [LessonController::class, 'getTimeSlot'])->name('lessons.timeSlot');
Route::get('/lessons', [LessonController::class, 'index'])->name('lessons.index');
Route::get('/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');

// Authenticated
Route::middleware('auth')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');

    // Employee + Admin
    Route::middleware('role:ADMIN,EMPLOYEE')->group(function () {
        Route::post('/lessons', [LessonController::class, 'create'])->name('lessons.create');
        Route::put('/lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
        Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');
    });

    // Admin only
    Route::middleware('role:ADMIN')->group(function () {
        Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
        Route::get('/employees', [AuthController::class, 'employees'])->name('employees.index');
        Route::delete('/employees/{id}', [AuthController::class, 'deleteEmployee'])->name('employees.delete');

        Route::post('/buildings', [BuildingController::class, 'store'])->name('buildings.store');
        Route::put('/buildings/{building}', [BuildingController::class, 'update'])->name('buildings.update');
        Route::delete('/buildings/{building}', [BuildingController::class, 'destroy'])->name('buildings.destroy');

        Route::post('/class-rooms', [ClassRoomController::class, 'store'])->name('classRooms.store');
        Route::put('/class-rooms/{classRoom}', [ClassRoomController::class, 'update'])->name('classRooms.update');
        Route::delete('/class-rooms/{classRoom}', [ClassRoomController::class, 'destroy'])->name('classRooms.destroy');

        Route::post('/disciplines', [DisciplineController::class, 'store'])->name('disciplines.store');
        Route::put('/disciplines/{discipline}', [DisciplineController::class, 'update'])->name('disciplines.update');
        Route::delete('/disciplines/{discipline}', [DisciplineController::class, 'destroy'])->name('disciplines.destroy');

        Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
        Route::put('/teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
        Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');
    });
});
