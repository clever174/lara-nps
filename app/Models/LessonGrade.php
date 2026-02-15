<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonGrade extends Model
{
    protected $fillable = [
        'rating',
        'lesson_title',
        'lesson_id',
        'course_title',
        'course_id',
        'comment',
        'user_id',
    ];

    protected $casts = [
        'rating' => 'integer',
        'lesson_id' => 'integer',
        'course_id' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
    ];

    protected $appends = ['created_at_ru'];


    protected function createdAtRu(): Attribute
    {
        return Attribute::get(function () {
            return $this->created_at
                ? $this->created_at->format('d.m.Y H:i')
                : null;
        });
    }
}
