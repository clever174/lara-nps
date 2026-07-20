<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAudioGradeRequest;
use App\Services\AudioGrade\AudioGradeService;
use Illuminate\Http\JsonResponse;

class AudioGradeController extends Controller
{
    public function __construct(
        private readonly AudioGradeService $audioGradeService,
    ) {
    }

    public function store(StoreAudioGradeRequest $request): JsonResponse
    {
        $audioGrade = $this->audioGradeService->handle($request->validated());

        return response()->json($audioGrade, 201);
    }
}
