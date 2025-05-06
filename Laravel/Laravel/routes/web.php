<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TeacherController;
use App\Http\Controllers\API\StudentController;
use App\Http\Controllers\API\SubjectController;
use App\Http\Controllers\API\LessonController;
use App\Http\Controllers\API\GradeController;
use App\Models\User; 

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::get('/teachers', [TeacherController::class, 'index'])->middleware('role:'.User::ROLE_CLIENT);
    Route::post('/teachers', [TeacherController::class, 'store'])->middleware('role:'.User::ROLE_MANAGER);
    Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])->middleware('role:'.User::ROLE_CLIENT);
    Route::put('/teachers/{teacher}', [TeacherController::class, 'update'])->middleware('role:'.User::ROLE_MANAGER);
    Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])->middleware('role:'.User::ROLE_ADMIN);

    Route::get('/students', [StudentController::class, 'index'])->middleware('role:'.User::ROLE_CLIENT);
    Route::post('/students', [StudentController::class, 'store'])->middleware('role:'.User::ROLE_MANAGER);
    Route::get('/students/{student}', [StudentController::class, 'show'])->middleware('role:'.User::ROLE_CLIENT);
    Route::put('/students/{student}', [StudentController::class, 'update'])->middleware('role:'.User::ROLE_MANAGER);
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->middleware('role:'.User::ROLE_ADMIN);
    
    Route::get('/subjects', [SubjectController::class, 'index'])->middleware('role:'.User::ROLE_CLIENT);
    Route::post('/subjects', [SubjectController::class, 'store'])->middleware('role:'.User::ROLE_MANAGER);
    Route::get('/subjects/{subject}', [SubjectController::class, 'show'])->middleware('role:'.User::ROLE_CLIENT);
    Route::put('/subjects/{subject}', [SubjectController::class, 'update'])->middleware('role:'.User::ROLE_MANAGER);
    Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->middleware('role:'.User::ROLE_ADMIN);

    Route::get('/lessons', [LessonController::class, 'index'])->middleware('role:'.User::ROLE_CLIENT);
    Route::post('/lessons', [LessonController::class, 'store'])->middleware('role:'.User::ROLE_MANAGER);
    Route::get('/lessons/{lesson}', [LessonController::class, 'show'])->middleware('role:'.User::ROLE_CLIENT);
    Route::put('/lessons/{lesson}', [LessonController::class, 'update'])->middleware('role:'.User::ROLE_MANAGER);
    Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy'])->middleware('role:'.User::ROLE_ADMIN);

    Route::get('/grades', [GradeController::class, 'index'])->middleware('role:'.User::ROLE_CLIENT);
    Route::post('/grades', [GradeController::class, 'store'])->middleware('role:'.User::ROLE_MANAGER);
    Route::get('/grades/{grade}', [GradeController::class, 'show'])->middleware('role:'.User::ROLE_CLIENT);
    Route::put('/grades/{grade}', [GradeController::class, 'update'])->middleware('role:'.User::ROLE_MANAGER);
    Route::delete('/grades/{grade}', [GradeController::class, 'destroy'])->middleware('role:'.User::ROLE_ADMIN);
});