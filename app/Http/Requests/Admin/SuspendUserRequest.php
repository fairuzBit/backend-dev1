<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SuspendUserRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'duration' => ['required', 'string', 'in:1 Hari,1 Minggu,1 Bulan']
        ];
    }

    public function messages(): array
    {
        return [
            'duration.required' => 'Durasi suspend wajib diisi.',
            'duration.in' => 'Durasi suspend tidak valid. Pilihan: 1 Hari, 1 Minggu, 1 Bulan.'
        ];
    }
}
