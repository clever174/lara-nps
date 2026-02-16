<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class DateRangeResolver
{
    /**
     * Возвращает [date_start, date_end] в формате Y-m-d.
     * Если даты не переданы — текущая неделя (пн-вс).
     */
    public function resolve(Request $request, string $period = 'week', string $startKey = 'date_start', string $endKey = 'date_end'): array
    {
        $dateStart = $request->query($startKey);
        $dateEnd   = $request->query($endKey);

        if (!$dateStart || !$dateEnd) {
            $today = CarbonImmutable::now();

            if ($period === 'month') {
                $start = $today->startOfMonth();
                $end   = $today->endOfMonth();
            } elseif ($period === 'year') {
                $start = $today->startOfYear();
                $end   = $today->endOfYear();
            } else {
                $start = $today->startOfWeek();
                $end   = $today->endOfWeek();
            }

            $dateStart = $start->toDateString();
            $dateEnd   = $end->toDateString();
        }

        return [$dateStart, $dateEnd];
    }
}
