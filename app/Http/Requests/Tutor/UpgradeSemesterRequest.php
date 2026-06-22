<?php

namespace App\Http\Requests\Tutor;

use Illuminate\Foundation\Http\FormRequest;

class UpgradeSemesterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transcript_files' => 'required|array|min:1',
            'transcript_files.*' => 'mimes:pdf|max:5120',
            'new_semester' => 'required|integer|min:2|max:14',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $newSemester = $this->input('new_semester');
            $currentSemester = $this->user()->tutor?->current_semester;

            if ($currentSemester && $newSemester) {
                if ($newSemester <= $currentSemester) {
                    $validator->errors()->add('new_semester', 'Semester tujuan harus lebih tinggi dari semester saat ini (' . $currentSemester . ').');
                }
            }
        });
    }
}
