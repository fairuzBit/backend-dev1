<?php

namespace App\Http\Requests\Tutor;

use Illuminate\Foundation\Http\FormRequest;

class StoreAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slots'                    => 'present|array|min:1',
            'slots.*.day_of_week'      => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'slots.*.master_slot_id'   => 'required|exists:master_slots,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $tutor = $this->user()->tutor;
            
            if ($tutor && (!$tutor->price || $tutor->price <= 0)) {
                $hasActiveSlot = false;
                $slots = $this->input('slots', []);
                
                foreach ($slots as $slot) {
                    if (isset($slot['is_active']) && $slot['is_active']) {
                        $hasActiveSlot = true;
                        break;
                    }
                }
                
                if ($hasActiveSlot) {
                    $validator->errors()->add('slots', 'Anda harus mengatur tarif mengajar di profil Anda terlebih dahulu sebelum dapat membuka jadwal ketersediaan.');
                }
            }
        });
    }
}
