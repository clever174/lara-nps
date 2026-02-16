<?php

use App\Http\Controllers\EmployeeGradeController;
use App\Http\Controllers\LessonGradeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



Route::get('/', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () { return Inertia::render('Dashboard'); });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/lesson-grades', [LessonGradeController::class, 'index'])->name('lesson-grades.index');
    Route::get('/lesson-grades/list', [LessonGradeController::class, 'list'])->name('lesson-grades.list');
    Route::post('/lesson-grades', [LessonGradeController::class, 'store'])->name('lesson-grades.store');

    Route::get('/lesson-grades/stats', [LessonGradeController::class, 'stats'])->name('lesson-grades.stats');

    Route::get('/employee-grades', [EmployeeGradeController::class, 'index'])->name('employee-grades.index');
    Route::get('/employee-grades/list', [EmployeeGradeController::class, 'list'])->name('employee-grades.list');

    Route::get('/mi/list', [EmployeeGradeController::class, 'list'])->name('mi.list');

});

require __DIR__.'/auth.php';
