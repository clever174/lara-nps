<?php

namespace App\Services\Mi;

use App\Models\Mi\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ListQuery
{
    public function paginate(): LengthAwarePaginator
    {
        $perPage = (int) request()->integer('per_page', 20);

        $query = Student::query()
            ->from('mi_student as s')
            ->leftJoin('mi_student_result as sr', function ($join) {
                $join->on('sr.student_id', '=', 's.id')
                    ->where('sr.is_actual', 1)
                    ->where('sr.feedback_grade', '>', 0);
            })
            ->select('s.*')
            ->selectRaw('COUNT(sr.feedback_grade) as grade_count')
            ->selectRaw('ROUND(AVG(sr.feedback_grade), 2) as avg_grade')
            ->groupBy(
                's.id',
                's.user_id',
                's.fio',
                's.general_feedback',
                's.ai_json',
                's.created_at',
                's.updated_at'
            );

        $this->applyFilters($query);
        $this->applySorting($query);

        return $query
            ->paginate($perPage)
            ->withQueryString();
    }

    private function applyFilters(Builder $query): void
    {
        $query->when(request('id'), fn (Builder $q, $v) =>
        $q->where('s.id', $v)
        );

        $query->when(request('user_id'), fn (Builder $q, $v) =>
        $q->where('s.user_id', $v)
        );

        $query->when(request('fio'), fn (Builder $q, $v) =>
        $q->where('s.fio', 'like', "%{$v}%")
        );

        // если используешь PrimeVue search.fio
        $query->when(request('search.fio'), fn (Builder $q, $v) =>
        $q->where('s.fio', 'like', "%{$v}%")
        );

        $query->when(request('date_start'), fn (Builder $q, $v) =>
        $q->whereDate('s.updated_at', '>=', $v)
        );

        $query->when(request('date_end'), fn (Builder $q, $v) =>
        $q->whereDate('s.updated_at', '<=', $v)
        );
    }

    private function applySorting(Builder $query): void
    {
        $sortField = request('sort_field');
        $sortOrder = (int) request('sort_order', -1); // PrimeVue: 1 asc, -1 desc

        $allowed = [
            'user_id',
            'fio',
            'updated_at',
            'grade_count',
            'avg_grade',
        ];

        if ($sortField && in_array($sortField, $allowed, true)) {
            $query->orderBy(
                $sortField === 'updated_at' ? 's.updated_at' : $sortField,
                $sortOrder === 1 ? 'asc' : 'desc'
            );
            return;
        }

        $query->orderByDesc('s.updated_at'); // default
    }
}
