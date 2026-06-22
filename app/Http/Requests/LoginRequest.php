<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
       public function rules(): array
    {
        return [
            // Samakan aturan emailnya dengan register
            'email'    => 'required|email',
            'password' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.starts_with' => 'Email mahasiswa harus diawali dengan angka 111.',
            'email.ends_with' => 'Hanya email mahasiswa UDINUS (@mhs.dinus.ac.id) yang diizinkan.',
            'password.required' => 'Password wajib diisi.'
        ];
    }

}
