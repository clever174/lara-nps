<?php

namespace App\Models\Mi;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $table = 'mi_student';

    protected $fillable = [
        'user_id',
        'fio',
        'general_feedback',
        'ai_json',
    ];

    protected $casts = [
        'general_feedback' => 'array', // json <-> array
        'ai_json'          => 'array',
    ];

    public function results(): HasMany
    {
        return $this->hasMany(StudentResult::class, 'student_id');
    }

    public function actualResults(): HasMany
    {
        return $this->results()->where('is_actual', true);
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
