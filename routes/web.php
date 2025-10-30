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
        Route::get('/search', 'searchBuilding')->name('buildings.search');
    });
});

Route::controller(LessonController::class)->group(function () {
    Route::prefix('lessons')->group(function () {
        Route::get('/create', 'create')->name('lessons.create');
    });
});


Route::controller(GroupController::class)->group(function () {
    Route::prefix('groups')->group(function () {
        Route::get('/search', 'searchGroups')->name('groups.search');
    });
});