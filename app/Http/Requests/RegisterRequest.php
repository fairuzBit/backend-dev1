<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'fullName' => 'required|string',
            'email' => 'required|email|unique:users|starts_with:111|ends_with:@mhs.dinus.ac.id', 
            'password' => 'required|min:8',
            'nim' => 'nullable|string|size:12|unique:users,nim',
            'phone' => 'nullable|string',
        ];
    }

    // Tambahkan fungsi messages ini untuk men-translate peringatan error
    public function messages(): array
    {
        return [
            'fullName.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'email.starts_with' => 'Email mahasiswa harus diawali dengan angka 111.',
            'email.ends_with' => 'Pendaftaran hanya diperbolehkan menggunakan email mahasiswa UDINUS (@mhs.dinus.ac.id).',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 8 karakter.',
            'nim.size' => 'NIM harus tepat berjumlah 12 karakter.',
            'nim.unique' => 'NIM ini sudah terdaftar.',
        ];
    }


}
