<?php

namespace App\Http\Resources\Tutor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TutorProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'bio' => $this->bio,
            'skills' => is_string($this->skills) ? json_decode($this->skills, true) : $this->skills,
            'price' => (float) $this->price,
            'ipk' => (float) $this->ipk,
            // Bisa menambahkan field lain sesuai kebutuhan Tutor
        ];
    }
}
