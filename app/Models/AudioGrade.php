<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AudioGrade extends Model
{
    protected $fillable = [
        'user_id',
        'fio',
        'attempt_number',
        'file_path',
        'ai_json',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'attempt_number' => 'integer',
            'ai_json' => 'array',
        ];
    }
}
