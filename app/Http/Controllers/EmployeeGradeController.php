<?php

namespace App\Http\Controllers;

use App\Services\LessonGrades\Analytics;
use App\Support\DateRangeResolver;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeGradeController extends Controller
{
    public function index(Request $request, Analytics $analytics, DateRangeResolver $dates): Response
    {
        [$dateStart, $dateEnd] = $dates->resolve($request);

        return Inertia::render('EmployeeGrades/Index', [
            'filters' => [
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
            ],
            'employeeData' => []
//            'employeeData' => $analytics->buildTree($dateStart, $dateEnd),
        ]);
    }
}
