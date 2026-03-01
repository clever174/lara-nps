<?php

namespace App\Services\Dashboard;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LessonStatsService
{
    public function get(): array
    {
        $row = DB::table('lesson_grades')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('ROUND(AVG(rating), 2) as avg_rating')
            ->selectRaw('SUM(CASE WHEN rating >= 9 THEN 1 ELSE 0 END) as promoters')
            ->selectRaw('SUM(CASE WHEN rating BETWEEN 7 AND 8 THEN 1 ELSE 0 END) as passives')
            ->selectRaw('SUM(CASE WHEN rating <= 6 THEN 1 ELSE 0 END) as detractors')
            ->selectRaw('
                ROUND(
                    (
                        SUM(rating BETWEEN 9 AND 10)
                        - SUM(rating BETWEEN 0 AND 6)
                    ) / NULLIF(COUNT(rating), 0) * 100,
                2) as nps
            ')
            ->first();

        return (array) $row;
    }
}
