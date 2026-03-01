<?php

namespace App\Services\Dashboard;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeStatsService
{
    public function get(): array
    {
        $row = DB::table('employee_grades')
            ->selectRaw('
                COUNT(*) as total,
                ROUND(AVG(rating), 2) as avg_rating,
                SUM(rating >= 9) as promoters,
                SUM(rating BETWEEN 7 AND 8) as passives,
                SUM(rating <= 6) as detractors,
                ROUND(
                    (
                        SUM(rating >= 9)
                        - SUM(rating <= 6)
                    ) / NULLIF(COUNT(rating), 0) * 100,
                2) as nps
            ')
            ->first();

        return (array) $row;
    }
}
