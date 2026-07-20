<?php

use App\Http\Controllers\Api\AudioGradeController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->group(function () {
    Route::get('/ping', function () {
        return response()->json(['ok' => true]);
    });

    Route::post('/audio-grades', [AudioGradeController::class, 'store']);
});
