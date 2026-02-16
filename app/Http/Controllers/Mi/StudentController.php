<?php

namespace App\Http\Controllers\Mi;

use App\Http\Controllers\Controller;
use App\Models\Mi\Student;
use App\Services\Mi\ListQuery;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    public function index(ListQuery $listQuery): Response
    {
        return Inertia::render('Mi/Students/List', [
            'students' => $listQuery->paginate(),
            'filters'  => request()->only([
                'id',
                'fio',
                'grade_count',
                'avg_grade',
                'updated_at',
                'user_id',
                'sort_field',
                'sort_order',
                'per_page',
                'date_start',
                'date_end',
            ]),
        ]);
    }

    public function show(\App\Models\Mi\Student $student): \Inertia\Response
    {
        // Студент из списка у тебя был с grade_count/avg_grade.
        // В карточке посчитаем тоже — чтобы не зависеть от списка.
        $stats = \App\Models\Mi\StudentResult::query()
            ->where('student_id', $student->id)
            ->where('is_actual', 1)
            ->where('feedback_grade', '>', 0)
            ->selectRaw('COUNT(*) as grade_count, ROUND(AVG(feedback_grade), 2) as avg_grade')
            ->first();

        $results = \App\Models\Mi\StudentResult::query()
            ->where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->get([
                'id',
                'attempt_number',
                'is_actual',
                'feedback_grade',
                'prompt',
                'feedback_comment',
                'feedback_recommendation',
                'created_at',
            ])
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'attempt_number' => $r->attempt_number,
                    'is_actual' => (bool) $r->is_actual,
                    'feedback_grade' => (int) $r->feedback_grade,
                    'prompt' => $r->prompt,
                    // В старом коде comment/recommendation были json строками.
                    // Здесь у тебя text, но по факту там JSON-массив — декодим.
                    'feedback_comment' => $this->tryJsonArray($r->feedback_comment),
                    'feedback_recommendation' => $this->tryJsonArray($r->feedback_recommendation),
                    'created_at' => optional($r->created_at)->toDateTimeString(),
                    'created_at_ru' => optional($r->created_at)->format('d.m.Y H:i:s'),
                ];
            });

        return \Inertia\Inertia::render('Mi/Students/Card', [
            'student' => [
                'id' => $student->id,
                'user_id' => $student->user_id,
                'fio' => $student->fio,
                'general_feedback' => $student->general_feedback, // cast array уже есть
                'grade_count' => (int) ($stats->grade_count ?? 0),
                'avg_grade' => (float) ($stats->avg_grade ?? 0),
            ],
            'results' => $results,
        ]);
    }

    /**
     * Если строка — JSON массива, вернём массив.
     * Иначе вернём массив из одной строки.
     */
    private function tryJsonArray(?string $value): array
    {
        $value = $value ?? '';
        $value = trim($value);

        if ($value === '') return [];

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return [$value];
    }

}
