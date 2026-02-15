<?php

namespace App\Services\LessonGrades;

use App\Models\LessonGrade;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ListQuery
{
    public function paginate(): LengthAwarePaginator
    {
        $perPage = (int) request()->integer('per_page', 20);

        $query = LessonGrade::query();

        $this->applyFilters($query);
        $this->applySorting($query);

        return $query
            ->select()
            ->paginate($perPage)
            ->withQueryString();
    }

    private function applyFilters(Builder $query): void
    {
        $query->when(request('course_id'), fn (Builder $q, $v) => $q->where('course_id', $v));
        $query->when(request('lesson_id'), fn (Builder $q, $v) => $q->where('lesson_id', $v));
        $query->when(request('rating'), fn (Builder $q, $v) => $q->where('rating', $v));
    }

    private function applySorting(Builder $query): void
    {
        $sortField = request('sort_field');
        $sortOrder = (int) request('sort_order', -1); // PrimeVue: 1 asc, -1 desc

        $allowed = ['user_id', 'course_title', 'lesson_title', 'created_at', 'rating'];

        if ($sortField && in_array($sortField, $allowed, true)) {
            $query->orderBy($sortField, $sortOrder === 1 ? 'asc' : 'desc');
            return;
        }

        $query->orderByDesc('created_at'); // default
    }
}
