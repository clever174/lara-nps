<?php

namespace App\Services\LessonGrades;

use App\Models\LessonGrade;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class Analytics
{
    public function buildTree(?string $dateStart, ?string $dateEnd): Collection
    {
        [$start, $endExclusive] = $this->makeRange($dateStart, $dateEnd);

        $baseQuery = LessonGrade::query()
            ->when($start, fn ($q) => $q->where('created_at', '>=', $start))
            ->when($endExclusive, fn ($q) => $q->where('created_at', '<', $endExclusive));

        $courseRows = (clone $baseQuery)
            ->selectRaw($this->courseSelect())
            ->groupBy('course_id')
            ->orderByDesc('quantity')
            ->get();

        $lessonsByCourseId = (clone $baseQuery)
            ->selectRaw($this->lessonSelect())
            ->groupBy('lesson_id', 'lesson_title', 'course_id', 'course_title')
            ->orderByRaw('CAST(lesson_title AS SIGNED) ASC, lesson_title ASC')
            ->get()
            ->groupBy('course_id');

        return $courseRows->map(function ($course) use ($lessonsByCourseId) {
            $courseId = (string) $course->course_id;

            $courseArr = $this->castNumeric($course->toArray());

            $children = ($lessonsByCourseId[$course->course_id] ?? collect())
                ->map(function ($lesson) use ($courseId) {
                    return [
                        'key' => $courseId . ':' . $lesson->lesson_id,
                        'data' => $this->castNumeric($lesson->toArray()),
                        'children' => [],
                    ];
                })
                ->values()
                ->all();

            return [
                'key' => $courseId,
                'data' => $courseArr,
                'children' => $children,
            ];
        })->values();
    }

    private function makeRange(?string $dateStart, ?string $dateEnd): array
    {
        $start = $dateStart
            ? CarbonImmutable::parse($dateStart)->startOfDay()
            : null;

        $endExclusive = $dateEnd
            ? CarbonImmutable::parse($dateEnd)->addDay()->startOfDay()
            : null;

        return [$start, $endExclusive];
    }

    private function castNumeric(array $data): array
    {
        foreach ($data as $k => $v) {
            if (is_numeric($v)) $data[$k] = $v + 0;
        }
        return $data;
    }

    private function courseSelect(): string
    {
        return '
            ANY_VALUE(course_title) AS course_title,
            course_id,
            COUNT(id) AS quantity,

            SUM(rating = 10) AS rating_10,
            SUM(rating = 9)  AS rating_9,
            SUM(rating = 8)  AS rating_8,
            SUM(rating = 7)  AS rating_7,
            SUM(rating = 6)  AS rating_6,
            SUM(rating = 5)  AS rating_5,
            SUM(rating = 4)  AS rating_4,
            SUM(rating = 3)  AS rating_3,
            SUM(rating = 2)  AS rating_2,
            SUM(rating = 1)  AS rating_1,
            SUM(rating = 0)  AS rating_0,

            ROUND(AVG(rating), 2) AS avg_rating,

            SUM(rating BETWEEN 0 AND 6)  AS detractors,
            SUM(rating BETWEEN 7 AND 8)  AS passives,
            SUM(rating BETWEEN 9 AND 10) AS promoters,

            COUNT(rating) AS rating_count,

            ROUND(
                (SUM(rating BETWEEN 9 AND 10) - SUM(rating BETWEEN 0 AND 6))
                / NULLIF(COUNT(rating), 0) * 100,
            2) AS nps
        ';
    }

    private function lessonSelect(): string
    {
        // то же самое, но + lesson_title, lesson_id
        return '
            course_title,
            course_id,
            lesson_title,
            lesson_id,
            COUNT(id) AS quantity,

            SUM(rating = 10) AS rating_10,
            SUM(rating = 9)  AS rating_9,
            SUM(rating = 8)  AS rating_8,
            SUM(rating = 7)  AS rating_7,
            SUM(rating = 6)  AS rating_6,
            SUM(rating = 5)  AS rating_5,
            SUM(rating = 4)  AS rating_4,
            SUM(rating = 3)  AS rating_3,
            SUM(rating = 2)  AS rating_2,
            SUM(rating = 1)  AS rating_1,
            SUM(rating = 0)  AS rating_0,

            ROUND(AVG(rating), 2) AS avg_rating,

            SUM(rating BETWEEN 0 AND 6)  AS detractors,
            SUM(rating BETWEEN 7 AND 8)  AS passives,
            SUM(rating BETWEEN 9 AND 10) AS promoters,

            COUNT(rating) AS rating_count,

            ROUND(
                (SUM(rating BETWEEN 9 AND 10) - SUM(rating BETWEEN 0 AND 6))
                / NULLIF(COUNT(rating), 0) * 100,
            2) AS nps
        ';
    }
}
