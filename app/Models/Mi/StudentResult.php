<?php

namespace App\Models\Mi;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentResult extends Model
{
    protected $table = 'mi_student_result';

    public $timestamps = false; // потому что есть только created_at (нет updated_at)

    protected $fillable = [
        'student_id',
        'attempt_number',
        'is_actual',
        'created_at',
        'feedback_grade',
        'prompt',
        'feedback_comment',
        'feedback_recommendation',
        'ai_json',
    ];

    protected $casts = [
        'is_actual' => 'bool',
        'ai_json'   => 'array',
        'created_at'=> 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    protected $appends = ['created_at_ru', 'updated_at_ru'];

    protected function createdAtRu(): Attribute
    {
        return Attribute::get(
            fn () => $this->created_at?->format('d.m.Y H:i')
        );
    }

    protected function updatedAtRu(): Attribute
    {
        return Attribute::get(
            fn () => $this->updated_at?->format('d.m.Y H:i')
        );
    }
}
