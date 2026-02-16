<?php

namespace App\Http\Controllers;

use App\Services\EmployeeGrades\Analytics;
use App\Services\EmployeeGrades\ListQuery;
use App\Support\DateRangeResolver;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeGradeController extends Controller
{
    public function index(Request $request, Analytics $analytics, DateRangeResolver $dates): Response
    {
        [$dateStart, $dateEnd] = $dates->resolve($request, 'month');

        return Inertia::render('EmployeeGrades/Index', [
            'filters' => [
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
            ],
            'employeeData' => $analytics->build($dateStart, $dateEnd)
        ]);
    }

    public function list(ListQuery $listQuery): Response
    {
        return Inertia::render('EmployeeGrades/List', [
            'employeeGrades' => $listQuery->paginate(),
            'filters' => request()->only([
                'employee_name', 'lesson_id', 'rating', 'user_id', 'course_title',
                'sort_field', 'sort_order',
                'per_page',
                'date_start', 'date_end',
            ]),
        ]);
    }
}
