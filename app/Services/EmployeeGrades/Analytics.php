<?php

namespace App\Services\EmployeeGrades;

use App\Models\EmployeeGrade;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class Analytics
{
    public function build(?string $dateStart, ?string $dateEnd): Collection
    {
        [$start, $endExclusive] = $this->makeRange($dateStart, $dateEnd);

        $query = EmployeeGrade::query()
            ->when($start, fn ($q) => $q->where('created_at', '>=', $start))
            ->when($endExclusive, fn ($q) => $q->where('created_at', '<', $endExclusive));

        return $query
            ->selectRaw($this->select())
            ->groupBy('employee_name')
            ->orderByDesc('quantity')
            ->get()
            ->map(fn ($row) => $this->castNumeric($row->toArray()))
            ->values();
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

    private function select(): string
    {
        return '
            employee_name,
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
