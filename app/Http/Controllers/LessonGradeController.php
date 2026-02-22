<?php

namespace App\Http\Controllers;

use App\Models\LessonGrade;
use App\Services\LessonGrades\Analytics;
use App\Services\LessonGrades\ListQuery;
use App\Support\DateRangeResolver;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LessonGradeController extends Controller
{
    public function index(Request $request, Analytics $analytics, DateRangeResolver $dates): Response
    {
        [$dateStart, $dateEnd] = $dates->resolve($request, 'month');

        return Inertia::render('LessonGrades/Index', [
            'filters' => [
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
            ],
            'courseData' => $analytics->buildTree($dateStart, $dateEnd),
        ]);
    }

    public function list(ListQuery $listQuery): Response
    {
        return Inertia::render('LessonGrades/List', [
            'lessonGrades' => $listQuery->paginate(),
            'filters' => request()->only([
                'course_id', 'lesson_id', 'rating', 'user_id',
                'sort_field', 'sort_order',
                'per_page',
                'date_start', 'date_end',
            ]),
        ]);
    }
}
