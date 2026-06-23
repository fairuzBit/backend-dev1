<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            // Validasi file gambar avatar (wajib jpeg/png/jpg, maksimal 2MB)
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg|max:10000', 
        ];
    }
    public function messages(): array
    {
        return [
            'avatar.image' => 'File yang di-upload harus berupa gambar.',
            'avatar.mimes' => 'Format gambar harus jpeg, png, atau jpg.',
            'avatar.max' => 'Ukuran gambar maksimal adalah 2MB.',
        ];
    }
}
