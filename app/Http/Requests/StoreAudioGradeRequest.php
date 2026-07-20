<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAudioGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
            'fio' => ['required', 'string'],
            'attempt_number' => ['required', 'integer'],
            'file_path' => ['required', 'string'],
        ];
    }
}
