<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeGrade extends Model
{
    protected $table = 'employee_grades';

    public $timestamps = false; // так как в таблице нет updated_at

    protected $fillable = [
        'rating',
        'employee_name',
        'employee_type',
        'user_id',
        'comment',
        'created_at',
        'course_title',
        'lesson_id',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'employee_type' => 'integer',
            'user_id' => 'integer',
            'created_at' => 'datetime',
            'lesson_id' => 'integer',
        ];
    }

    protected $appends = ['created_at_ru'];

    protected function createdAtRu(): Attribute
    {
        return Attribute::get(
            fn () => $this->created_at?->format('d.m.Y H:i')
        );
    }
}
