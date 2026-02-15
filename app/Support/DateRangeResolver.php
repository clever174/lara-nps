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
    public function resolve(Request $request, string $startKey = 'date_start', string $endKey = 'date_end'): array
    {
        $dateStart = $request->query($startKey);
        $dateEnd   = $request->query($endKey);

        if (!$dateStart || !$dateEnd) {
            $today = CarbonImmutable::now();

            $dateStart = $today->startOfWeek()->toDateString();
            $dateEnd   = $today->endOfWeek()->toDateString();
        }

        return [$dateStart, $dateEnd];
    }
}
