<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\ClassRoomController;
use App\Http\Controllers\DisciplineController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;
use PHPUnit\TextUI\Configuration\GroupCollection;

Route::controller(AppController::class)->group(function () {
    Route::get('/', 'index')->name('index');
});

Route::controller(TeacherController::class)->group(function () {
    Route::prefix('teachers')->group(function () {
        Route::get('/search', 'searchTeachers')->name('teachers.search');
    });
});

Route::controller(DisciplineController::class)->group(function () {
    Route::prefix('disciplines')->group(function () {
        Route::get('/search', 'searchDisciplines')->name('disciplines.search');
    });
});

Route::controller(ClassRoomController::class)->group(function () {
    Route::prefix('class-rooms')->group(function () {
        Route::get('/search', 'searchClassRooms')->name('classRooms.search');
    });
});

Route::controller(BuildingController::class)->group(function () {
    Route::prefix('buildings')->group(function () {
        Route::get('/search', 'searchBuildings')->name('buildings.search');
    });
});

Route::controller(LessonController::class)->group(function () {
    Route::prefix('lessons')->group(function () {
        Route::post('/create', 'create')->name('lessons.create');
        Route::put('/update/{id}', 'update')->name('lessons.update');
        Route::get('/schedule', 'getSchedule')->name('lessons.schedule');
        Route::get('/time-slot', 'getTimeSlot')->name('lessons.timeSlot');
    });
});


Route::controller(GroupController::class)->group(function () {
    Route::prefix('groups')->group(function () {
        Route::get('/search', 'searchGroups')->name('groups.search');
        Route::get('/search-by-name', 'searchGroupsByName')->name('groups.search');
    });
});