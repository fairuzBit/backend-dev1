<?php

namespace App\Http\Requests\Learner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'nim' => 'required|string|size:12|unique:users,nim,' . $this->user()->id,
            'email' => 'nullable|email|unique:users,email,' . $this->user()->id,
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
     }

     public function messages(): array
     {
         return [
             'nim.required' => 'NIM wajib diisi.',
             'nim.size' => 'NIM harus tepat berjumlah 12 karakter.',
             'nim.unique' => 'NIM ini sudah terdaftar.',
         ];
     }
}
