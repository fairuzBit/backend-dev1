<?php

namespace App\Http\Requests\Learner;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
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
            'tutor_id' => 'required|exists:tutors,id',
            'course_id' => 'required|exists:courses,id',
            'booking_date' => 'required|date|after_or_equal:today|before_or_equal:+30 days',
            'slot_ids' => 'required|array|min:1',
            'slot_ids.*' => 'exists:master_slots,id'
        ];
    }
}
