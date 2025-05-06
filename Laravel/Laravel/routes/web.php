<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TeacherController;
use App\Http\Controllers\API\StudentController;
use App\Http\Controllers\API\SubjectController;
use App\Http\Controllers\API\LessonController;
use App\Http\Controllers\API\GradeController;

Route::apiResource('teachers', TeacherController::class);
Route::apiResource('students', StudentController::class);
Route::apiResource('subjects', SubjectController::class);
Route::apiResource('lessons', LessonController::class);
Route::apiResource('grades', GradeController::class);
Route::get('/', function () {
    return view('welcome');
});
Route::apiResource('demo-items', DemoItemController::class);