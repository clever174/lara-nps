<?php

namespace App\Services\Students;

use App\Models\StudentResult;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class StudentStat
{
    public function attemptsTable(?string $dateStart, ?string $dateEnd): Collection
    {
        [$start, $endExclusive] = $this->makeRange($dateStart, $dateEnd);

        return StudentResult::query()
            ->where('is_actual', 1)
            ->where('feedback_grade', '>', 0)
            ->when($start, fn ($q) => $q->where('created_at', '>=', $start))
            ->when($endExclusive, fn ($q) => $q->where('created_at', '<', $endExclusive))
            ->selectRaw('
                attempt_number as attempt,
                COUNT(*) as qty,
                ROUND(AVG(feedback_grade), 2) as avg
            ')
            ->groupBy('attempt_number')
            ->orderBy('attempt_number')
            ->get()
            ->map(fn ($row) => [
                'attempt' => (int) $row->attempt,
                'qty'     => (int) $row->qty,
                'avg'     => (float) $row->avg,
            ]);
    }

    private function makeRange(?string $dateStart, ?string $dateEnd): array
    {
        if (!$dateStart || !$dateEnd) {
            return [null, null];
        }

        $start = CarbonImmutable::parse($dateStart)->startOfDay();
        $endExclusive = CarbonImmutable::parse($dateEnd)->addDay()->startOfDay();

        return [$start, $endExclusive];
    }
}
