<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\EmployeeStatsService;
use App\Services\Dashboard\LessonStatsService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(
        LessonStatsService $lessonStatsService,
        EmployeeStatsService $employeeStatsService,
    ): Response
    {
        return Inertia::render(
            'Dashboard',
            [
                'lesson' => $lessonStatsService->get(),
                'employee' => $employeeStatsService->get(),
            ]);
    }
}
