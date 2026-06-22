<?php

namespace App\Http\Requests\Tutor;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Course;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transcript_files' => 'required|array|min:1',
            'transcript_files.*' => 'mimes:pdf|max:5120', // Maks 5MB per file
            'course_ids' => 'required|array|min:1',
            'course_ids.*' => 'exists:courses,id',
            'current_semester' => 'required|integer|min:2|max:14',
            'portfolio_urls' => 'nullable|array',
            'portfolio_urls.*' => 'url|max:255',
            'skills' => 'nullable|json',
            'bio' => 'nullable|string|max:1000',
            'certificate_files' => 'nullable|array',
            'certificate_files.*' => 'mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $courseIds = $this->input('course_ids', []);
            $currentSemester = $this->input('current_semester');

            if (!empty($courseIds) && $currentSemester) {
                $invalidCourses = Course::whereIn('id', $courseIds)
                                        ->where('semester', '>=', $currentSemester)
                                        ->exists();
                
                if ($invalidCourses) {
                    $validator->errors()->add('course_ids', 'Tutor hanya dapat mengajar mata kuliah yang semesternya berada di bawah semester yang sedang ditempuh.');
                }
            }
        });
    }
}
