<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->group(function () {
    Route::get('/ping', function () {
        return response()->json(['ok' => true]);
    });
});
